<?php

declare(strict_types=1);

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\Tests\DependencyInjection;

use AppInsightsPHP\Symfony\AppInsightsPHPBundle\DependencyInjection\Configuration;
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
    }

    protected function process($configs)
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }
}
