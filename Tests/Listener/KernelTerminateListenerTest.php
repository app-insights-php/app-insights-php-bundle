<?php

declare (strict_types=1);

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\Tests\Listener;

use AppInsightsPHP\Client\Client;
use AppInsightsPHP\Client\Configuration;
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Listener\KernelTerminateListener;
use ApplicationInsights\Channel\Telemetry_Channel;
use ApplicationInsights\Telemetry_Client;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

final class KernelTerminateListenerTest extends TestCase
{
    public function test_do_nothing_when_telemetry_queue_is_empty()
    {
        $client = new Client($telemetryClientMock = $this->createMock(Telemetry_Client::class), Configuration::createDefault());

        $telemetryClientMock->method('getChannel')->willReturn($telemetryChannelMock = $this->createMock(Telemetry_Channel::class));
        $telemetryChannelMock->method('getQueue')->willReturn([]);

        $telemetryClientMock->expects($this->never())
            ->method('flush');

        $listener = new KernelTerminateListener($client);

        $listener->onTerminate();
    }

    public function test_successful_flush()
    {
        $client = new Client($telemetryClientMock = $this->createMock(Telemetry_Client::class), Configuration::createDefault());

        $telemetryClientMock->method('getChannel')->willReturn($telemetryChannelMock = $this->createMock(Telemetry_Channel::class));
        $telemetryChannelMock->method('getQueue')->willReturn(['some_log_entry']);

        $telemetryClientMock->expects($this->once())
            ->method('flush');

        $listener = new KernelTerminateListener($client);

        $listener->onTerminate();
    }

    public function test_fallback_logger_during_flush_unexpected_exception()
    {
        $client = new Client($telemetryClientMock = $this->createMock(Telemetry_Client::class), Configuration::createDefault());

        $telemetryClientMock->method('getChannel')->willReturn($telemetryChannelMock = $this->createMock(Telemetry_Channel::class));
        $telemetryChannelMock->method('getQueue')->willReturn(['some_log_entry']);
        $telemetryChannelMock->method('getSerializedQueue')->willReturn(\json_encode(['some_log_entry']));

        $telemetryClientMock->method('flush')
            ->willThrowException(new \RuntimeException('Unexpected API exception'));

        $listener = new KernelTerminateListener($client, null, $loggerMock = $this->createMock(LoggerInterface::class));

        $loggerMock->expects($this->once())
            ->method('error')
            ->with('Exception occurred while flushing App Insights Telemetry Client: Unexpected API exception', ['some_log_entry']);

        $listener->onTerminate();
    }

    public function test_adding_queue_to_failure_cache_on_unexpected_api_exception_and_cache_is_empty()
    {
        $client = new Client($telemetryClientMock = $this->createMock(Telemetry_Client::class), Configuration::createDefault());

        $telemetryClientMock->method('getChannel')->willReturn($telemetryChannelMock = $this->createMock(Telemetry_Channel::class));
        $telemetryChannelMock->method('getQueue')->willReturn(['some_log_entry']);

        $telemetryClientMock->method('flush')
            ->willThrowException(new \RuntimeException('Unexpected API exception'));

        $listener = new KernelTerminateListener($client, $cacheMock = $this->createMock(CacheInterface::class));

        $cacheMock->method('has')
            ->willReturn(false);

        $cacheMock->expects($this->once())
            ->method('set')
            ->with(KernelTerminateListener::CACHE_CHANNEL_KEY, serialize(['some_log_entry']), KernelTerminateListener::CACHE_CHANNEL_TTL_SEC);

        $listener->onTerminate();
    }

    public function test_adding_queue_to_failure_cache_on_unexpected_api_exception_and_cache_is_not_empty()
    {
        $client = new Client($telemetryClientMock = $this->createMock(Telemetry_Client::class), Configuration::createDefault());

        $telemetryClientMock->method('getChannel')->willReturn($telemetryChannelMock = $this->createMock(Telemetry_Channel::class));
        $telemetryChannelMock->method('getQueue')->willReturn(['some_log_entry']);

        $telemetryClientMock->method('flush')
            ->willThrowException(new \RuntimeException('Unexpected API exception'));

        $listener = new KernelTerminateListener($client, $cacheMock = $this->createMock(CacheInterface::class));

        $cacheMock->method('has')
            ->willReturn(true);

        $cacheMock->method('get')
            ->willReturn(serialize(['some_older_entry']));

        $cacheMock->expects($this->once())
            ->method('set')
            ->with(KernelTerminateListener::CACHE_CHANNEL_KEY, serialize(['some_older_entry', 'some_log_entry']), KernelTerminateListener::CACHE_CHANNEL_TTL_SEC);

        $listener->onTerminate();
    }

    public function test_flush_when_cache_is_not_empty()
    {
        $client = new Client($telemetryClientMock = $this->createMock(Telemetry_Client::class), Configuration::createDefault());

        $telemetryClientMock->method('getChannel')->willReturn($telemetryChannelMock = $this->createMock(Telemetry_Channel::class));
        $telemetryChannelMock->method('getQueue')->willReturn(['some_log_entry']);

        $listener = new KernelTerminateListener($client, $cacheMock = $this->createMock(CacheInterface::class));

        $cacheMock->method('has')
            ->willReturn(true);

        $cacheMock->method('get')
            ->willReturn(serialize(['some_older_entry']));

        $cacheMock->expects($this->once())
            ->method('delete')
            ->with(KernelTerminateListener::CACHE_CHANNEL_KEY);

        $telemetryChannelMock->expects($this->once())
            ->method('setQueue')
            ->with(['some_older_entry', 'some_log_entry']);

        $telemetryClientMock->expects($this->once())
            ->method('flush');

        $listener->onTerminate();
    }
}