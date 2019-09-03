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

use AppInsightsPHP\Symfony\AppInsightsPHPBundle\DependencyInjection\Configuration;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends Testcase
{
    public function test_default_configuration()
    {
        $configs = [
            [
                'instrumentation_key' => 'test_key',
                'failure_cache_service_id' => 'failure_cache_id',
            ],
        ];

        $config = $this->process($configs);

        $this->assertEquals('test_key', $config['instrumentation_key']);
        $this->assertEquals('failure_cache_id', $config['failure_cache_service_id']);
        $this->assertTrue($config['enabled']);
        $this->assertFalse($config['gzip_enabled']);
        $this->assertEquals([], $config['exceptions']['ignored_exceptions']);
        $this->assertFalse($config['doctrine']['track_dependency']);
        $this->assertEmpty($config['monolog']['handlers']);
    }

    public function test_monolog_configuration()
    {
        $configs = [
            [
                'instrumentation_key' => 'test_key',
                'failure_cache_service_id' => 'failure_cache_id',
                'monolog' => [
                    'handlers' => [
                        [
                            'type' => 'trace',
                            'name' => 'foo.logger',
                            'level' => Logger::DEBUG,
                        ],
                        [
                            'name' => 'bar.logger',
                            'type' => 'dependency',
                        ],
                    ],
                ],
            ],
        ];

        $config = $this->process($configs);

        $this->assertArrayHasKey('foo.logger', $config['monolog']['handlers']);
        $this->assertEquals(Logger::DEBUG, $config['monolog']['handlers']['foo.logger']['level']);
        $this->assertTrue($config['monolog']['handlers']['foo.logger']['bubble']);
        $this->assertEquals('trace', $config['monolog']['handlers']['foo.logger']['type']);
        $this->assertEquals('dependency', $config['monolog']['handlers']['bar.logger']['type']);
    }

    public function test_gzip_configuration()
    {
        $configs = [
            [
                'instrumentation_key' => 'test_key',
                'failure_cache_service_id' => 'failure_cache_id',
                'gzip_enabled' => true,
            ],
        ];

        $config = $this->process($configs);

        $this->assertTrue($config['gzip_enabled']);
    }

    protected function process($configs)
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }
}
