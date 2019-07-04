<?php

declare(strict_types=1);

/*
 * This file is part of the App Insights PHP project.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\Tests\DependencyInjection;

use AppInsightsPHP\Client\Client;
use AppInsightsPHP\Doctrine\DBAL\Logging\DependencyLogger;
use AppInsightsPHP\Monolog\Handler\AppInsightsTraceHandler;
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\DependencyInjection\AppInsightsPHPExtension;
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Twig\TelemetryExtension;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;

final class AppInsightsPHPExtensionTest extends TestCase
{
    private $kernel;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel = $this->getMockBuilder(KernelInterface::class)->getMock();
        $this->container = new ContainerBuilder();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->container = null;
        $this->kernel = null;
    }

    public function test_default_configuration()
    {
        $extension = new AppInsightsPHPExtension();
        $extension->load(
            [[
                'instrumentation_key' => 'test_key',
            ]],
            $this->container
        );

        $this->assertTrue($this->container->hasDefinition('app_insights_php.telemetry'));
        $this->assertTrue($this->container->hasParameter('app_insights_php.instrumentation_key'));

        $this->assertFalse($this->container->hasDefinition('app_insights_php.doctrine.logger.app_insights'));

        $this->assertTrue($this->container->get('app_insights_php.configuration')->isEnabled());
        $this->assertTrue($this->container->get('app_insights_php.configuration.exceptions')->isEnabled());
        $this->assertTrue($this->container->get('app_insights_php.configuration.traces')->isEnabled());
        $this->assertTrue($this->container->get('app_insights_php.configuration.dependencies')->isEnabled());
        $this->assertTrue($this->container->get('app_insights_php.configuration.requests')->isEnabled());

        $this->assertTrue($this->container->hasDefinition('app_insights_php.symfony.listener.http_request'));
        $this->assertTrue($this->container->hasDefinition('app_insights_php.symfony.listener.kernel_terminate'));
        $this->assertTrue($this->container->hasDefinition('app_insights_php.symfony.listener.exception'));

        $this->assertInstanceOf(Client::class, $this->container->get('app_insights_php.telemetry'));
    }

    public function test_configuration_when_enabled_is_set_to_false()
    {
        $extension = new AppInsightsPHPExtension();
        $extension->load(
            [[
                'enabled' => false,
                'instrumentation_key' => 'test_key',
            ]],
            $this->container
        );

        $this->assertTrue($this->container->hasDefinition('app_insights_php.telemetry'));
        $this->assertTrue($this->container->hasParameter('app_insights_php.instrumentation_key'));

        $this->assertFalse($this->container->hasDefinition('app_insights_php.doctrine.logger.app_insights'));

        $this->assertFalse($this->container->get('app_insights_php.configuration')->isEnabled());
        $this->assertTrue($this->container->get('app_insights_php.configuration.exceptions')->isEnabled());
        $this->assertTrue($this->container->get('app_insights_php.configuration.traces')->isEnabled());
        $this->assertTrue($this->container->get('app_insights_php.configuration.dependencies')->isEnabled());
        $this->assertTrue($this->container->get('app_insights_php.configuration.requests')->isEnabled());

        $this->assertFalse($this->container->hasDefinition('app_insights_php.symfony.listener.http_request'));
        $this->assertFalse($this->container->hasDefinition('app_insights_php.symfony.listener.kernel_terminate'));
        $this->assertFalse($this->container->hasDefinition('app_insights_php.symfony.listener.exception'));

        $this->assertInstanceOf(Client::class, $this->container->get('app_insights_php.telemetry'));
    }

    public function test_fallback_logger_configuration()
    {
        $extension = new AppInsightsPHPExtension();
        $extension->load(
            [[
                'instrumentation_key' => 'test_key',
                'fallback_logger' => [
                    'service_id' => 'logger',
                ],
            ]],
            $this->container
        );

        $this->assertSame(
            'logger',
            (string) $this->container->getDefinition('app_insights_php.telemetry.factory')->getArgument(3)
        );
    }

    public function test_fallback_logger_with_monolog_channel_configuration()
    {
        $extension = new AppInsightsPHPExtension();
        $extension->load(
            [[
                'instrumentation_key' => 'test_key',
                'fallback_logger' => [
                    'service_id' => 'logger',
                    'monolog_channel' => 'main',
                ],
            ]],
            $this->container
        );

        $this->assertSame(
            'logger',
            (string) $this->container->getDefinition('app_insights_php.telemetry.factory')->getArgument(3)
        );
        $this->assertSame(
            'main',
            $this->container->getDefinition('app_insights_php.telemetry.factory')->getTag('monolog.logger')[0]['channel']
        );
    }

    public function test_failure_cache_configuration()
    {
        $extension = new AppInsightsPHPExtension();
        $extension->load(
            [[
                'instrumentation_key' => 'test_key',
                'failure_cache_service_id' => 'failure_cache_id',
            ]],
            $this->container
        );

        $this->assertSame(
            'failure_cache_id',
            (string) $this->container->getDefinition('app_insights_php.telemetry.factory')->getArgument(2)
        );
    }

    public function test_doctrine_logger_configuration()
    {
        $extension = new AppInsightsPHPExtension();
        $extension->load(
            [[
                'instrumentation_key' => 'test_key',
                'doctrine' => [
                    'track_dependency' => true,
                ],
            ]],
            $this->container
        );

        $this->assertTrue($this->container->hasDefinition('app_insights_php.doctrine.logger.dependency'));
        $this->assertInstanceOf(DependencyLogger::class, $this->container->get('app_insights_php.doctrine.logger.dependency'));
    }

    public function test_twig_configuration()
    {
        $extension = new AppInsightsPHPExtension();
        $extension->load(
            [[
                'instrumentation_key' => 'test_key',
            ]],
            $this->container
        );

        $this->assertInstanceOf(TelemetryExtension::class, $this->container->get('app_insights_php.twig.telemetry'));
    }

    public function test_ignored_exceptions_configuration()
    {
        $extension = new AppInsightsPHPExtension();
        $extension->load(
            [[
                'instrumentation_key' => 'test_key',
                'exceptions' => [
                    'ignored_exceptions' => [\RuntimeException::class],
                ],
            ]],
            $this->container
        );

        $this->assertTrue($this->container->get('app_insights_php.configuration')->exceptions()->isIgnored(\RuntimeException::class));
        $this->assertFalse($this->container->get('app_insights_php.configuration')->exceptions()->isIgnored(\Exception::class));
    }

    public function test_monolog_configuration()
    {
        $extension = new AppInsightsPHPExtension();
        $extension->load(
            [[
                'instrumentation_key' => 'test_key',
                'monolog' => [
                    'handlers' => [
                        [
                            'name' => 'foo.logger',
                            'level' => Logger::DEBUG,
                        ],
                    ],
                ],
            ]],
            $this->container
        );

        $this->assertInstanceOf(AppInsightsTraceHandler::class, $this->container->get('app_insights_php.monolog.handler.foo.logger'));
    }
}
