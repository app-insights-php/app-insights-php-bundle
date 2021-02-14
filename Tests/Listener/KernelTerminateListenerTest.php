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

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\Tests\Listener;

use AppInsightsPHP\Client\Client;
use AppInsightsPHP\Client\Configuration;
use AppInsightsPHP\Client\FailureCache;
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Listener\KernelTerminateListener;
use ApplicationInsights\Channel\Telemetry_Channel;
use ApplicationInsights\Telemetry_Client;
use ApplicationInsights\Telemetry_Context;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;

final class KernelTerminateListenerTest extends TestCase
{
    public function test_do_nothing_when_instrumentation_key_is_empty() : void
    {
        $telemetryClientMock = $this->createMock(Telemetry_Client::class);
        $telemetryClientMock->method('getChannel')->willReturn($telemetryChannelMock = $this->createMock(Telemetry_Channel::class));
        $telemetryClientMock->method('getContext')->willReturn($telemetryContextMock = $this->createMock(Telemetry_Context::class));
        $telemetryContextMock->method('getInstrumentationKey')->willReturn('');
        $telemetryChannelMock->method('getQueue')->willReturn([]);

        $client = new Client(
            $telemetryClientMock,
            Configuration::createDefault(),
            new FailureCache($this->createMock(CacheInterface::class)),
            new NullLogger()
        );

        $telemetryClientMock->expects($this->never())
            ->method('flush');

        $listener = new KernelTerminateListener($client);

        $listener->onTerminate();
    }

    public function test_do_nothing_when_telemetry_queue_is_empty() : void
    {
        $telemetryClientMock = $this->createMock(Telemetry_Client::class);
        $telemetryClientMock->method('getChannel')->willReturn($telemetryChannelMock = $this->createMock(Telemetry_Channel::class));
        $telemetryClientMock->method('getContext')->willReturn($telemetryContextMock = $this->createMock(Telemetry_Context::class));
        $telemetryContextMock->method('getInstrumentationKey')->willReturn('instrumentation_key');
        $telemetryChannelMock->method('getQueue')->willReturn([]);

        $client = new Client(
            $telemetryClientMock,
            Configuration::createDefault(),
            new FailureCache($this->createMock(CacheInterface::class)),
            new NullLogger()
        );

        $telemetryClientMock->expects($this->never())
            ->method('flush');

        $listener = new KernelTerminateListener($client);

        $listener->onTerminate();
    }

    public function test_successful_flush() : void
    {
        $telemetryClientMock = $this->createMock(Telemetry_Client::class);
        $telemetryClientMock->method('getChannel')->willReturn($telemetryChannelMock = $this->createMock(Telemetry_Channel::class));
        $telemetryClientMock->method('getContext')->willReturn($telemetryContextMock = $this->createMock(Telemetry_Context::class));
        $telemetryContextMock->method('getInstrumentationKey')->willReturn('instrumentation_key');
        $telemetryChannelMock->method('getQueue')->willReturn(['some_log_entry']);

        $client = new Client(
            $telemetryClientMock,
            Configuration::createDefault(),
            new FailureCache($this->createMock(CacheInterface::class)),
            new NullLogger()
        );

        $telemetryClientMock->expects($this->once())
            ->method('flush');

        $listener = new KernelTerminateListener($client);

        $listener->onTerminate();
    }
}
