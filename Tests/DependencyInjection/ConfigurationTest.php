<?php

declare(strict_types=1);

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
            ],
        ];

        $config = $this->process($configs);

        $this->assertEquals('test_key', $config['instrumentation_key']);
        $this->assertTrue($config['enabled']);
        $this->assertEquals([], $config['exceptions']['ignored_exceptions']);
        $this->assertFalse($config['doctrine']['track_dependency']);
        $this->assertEmpty($config['monolog']['handlers']);
    }

    public function test_monolog_configuration()
    {
        $configs = [
            [
                'instrumentation_key' => 'test_key',
                'monolog' => [
                    'handlers' => [
                        [
                            'name' => 'foo.logger',
                            'level' => Logger::DEBUG,
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
    }

    protected function process($configs)
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }
}
