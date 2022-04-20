<?php declare(strict_types=1);

/*
 * This file is part of the App Insights PHP project.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DependencyInjection\Compiler;

use AppInsightsPHP\Symfony\AppInsightsPHPBundle\DependencyInjection\Compiler\DoctrineDependencyPassPass;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Logging\LoggerChain;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineDependencyPassPassTest extends TestCase
{
    public const DEFAULT_CONNECTION = 'doctrine.dbal.default_connection';

    public const DEFAULT_CONNECTION_CONFIGURATION = 'doctrine.dbal.default_connection.configuration';

    /**
     * @var ContainerBuilder
     */
    protected $container;

    protected function setUp() : void
    {
        parent::setUp();

        $this->container = new ContainerBuilder();
        $this->container->setParameter('app_insights_php.doctrine.track_dependency', true);
        $this->container->setParameter('doctrine.connections', ['default' => self::DEFAULT_CONNECTION]);

        $this->container->register(self::DEFAULT_CONNECTION_CONFIGURATION);
    }

    public function testProcess() : void
    {
        $this->process($this->container);

        $doctrineConfigurationMethods = $this->container->getDefinition(self::DEFAULT_CONNECTION_CONFIGURATION)
            ->getMethodCalls();

        $this->assertCount(1, $doctrineConfigurationMethods);
        $this->assertEquals('setSQLLogger', $doctrineConfigurationMethods[0][0]);
        $this->assertEquals(
            [new Reference('app_insights_php.doctrine.logger.dependency')],
            $doctrineConfigurationMethods[0][1]
        );
    }

    public function testProcessWithEmptySetLoggerChain() : void
    {
        $this->container->register('doctrine.dbal.logger.chain', LoggerChain::class);
        $loggerChainDefinition = $this->container->getDefinition('doctrine.dbal.logger.chain')->setArgument(0, []);

        $this->container->getDefinition(self::DEFAULT_CONNECTION_CONFIGURATION)
            ->addMethodCall('setSQLLogger', [new Reference('doctrine.dbal.logger.chain')]);

        $this->process($this->container);

        $doctrineConfigurationMethods = $this->container->getDefinition(self::DEFAULT_CONNECTION_CONFIGURATION)
            ->getMethodCalls();

        $this->assertCount(1, $doctrineConfigurationMethods);
        $this->assertEquals('setSQLLogger', $doctrineConfigurationMethods[0][0]);
        $this->assertEquals(
            [new Reference('doctrine.dbal.logger.chain')],
            $doctrineConfigurationMethods[0][1]
        );

        $this->assertEquals(
            [new Reference('app_insights_php.doctrine.logger.dependency')],
            $loggerChainDefinition->getArgument(0)
        );
    }

    public function testProcessWithSetLoggerChain() : void
    {
        $this->container->register('doctrine.dbal.logger.chain', LoggerChain::class);
        $this->container->register('doctrine.dbal.logger.profiling', DebugStack::class);
        $loggerChainDefinition = $this->container->getDefinition('doctrine.dbal.logger.chain')
            ->setArgument(0, [new Reference('doctrine.dbal.logger.profiling')]);

        $this->container->getDefinition(self::DEFAULT_CONNECTION_CONFIGURATION)
            ->addMethodCall('setSQLLogger', [new Reference('doctrine.dbal.logger.chain')]);

        $this->process($this->container);

        $doctrineConfigurationMethods = $this->container->getDefinition(self::DEFAULT_CONNECTION_CONFIGURATION)
            ->getMethodCalls();

        $this->assertCount(1, $doctrineConfigurationMethods);
        $this->assertEquals('setSQLLogger', $doctrineConfigurationMethods[0][0]);
        $this->assertEquals(
            [new Reference('doctrine.dbal.logger.chain')],
            $doctrineConfigurationMethods[0][1]
        );

        $this->assertEquals(
            [
                new Reference('doctrine.dbal.logger.profiling'),
                new Reference('app_insights_php.doctrine.logger.dependency'),
            ],
            $loggerChainDefinition->getArgument(0)
        );
    }

    public function testProcessWithSetLogger() : void
    {
        $this->container->register('doctrine.dbal.logger.profiling', DebugStack::class);
        $this->container->getDefinition(self::DEFAULT_CONNECTION_CONFIGURATION)
            ->addMethodCall('setSQLLogger', [new Reference('doctrine.dbal.logger.profiling')]);

        $this->process($this->container);

        $doctrineConfigurationMethods = $this->container->getDefinition(self::DEFAULT_CONNECTION_CONFIGURATION)
            ->getMethodCalls();

        $expectedLoggerChainName = 'doctrine.dbal.logger.chain.default';

        $this->assertCount(1, $doctrineConfigurationMethods);
        $doctrineConfigurationMethod = \current($doctrineConfigurationMethods);
        $this->assertEquals('setSQLLogger', $doctrineConfigurationMethod[0]);
        $this->assertEquals(
            [new Reference($expectedLoggerChainName)],
            $doctrineConfigurationMethod[1]
        );

        $this->assertEquals(
            [
                new Reference('doctrine.dbal.logger.profiling'),
                new Reference('app_insights_php.doctrine.logger.dependency'),
            ],
            $this->container->getDefinition($expectedLoggerChainName)->getArgument(0)
        );
    }

    protected function process(ContainerBuilder $container) : void
    {
        (new DoctrineDependencyPassPass())->process($container);
    }
}
