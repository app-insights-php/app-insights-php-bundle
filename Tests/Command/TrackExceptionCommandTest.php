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

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\Tests\Command;

use AppInsightsPHP\Client\Client;
use AppInsightsPHP\Client\Configuration;
use AppInsightsPHP\Client\FailureCache;
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Command\TrackExceptionCommand;
use ApplicationInsights\Channel\Telemetry_Channel;
use ApplicationInsights\Telemetry_Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class TrackExceptionCommandTest extends TestCase
{
    public function test_tracking_metric() : void
    {
        $telemetryClientMock = $this->createMock(Telemetry_Client::class);
        $telemetryClientMock->method('getChannel')->willReturn($telemetryChannelMock = $this->createMock(Telemetry_Channel::class));

        $client = new Client(
            $telemetryClientMock,
            Configuration::createDefault(),
            new FailureCache($this->createMock(CacheInterface::class)),
            new NullLogger()
        );

        $application = new Application();
        $application->add(new TrackexceptionCommand($client));

        $tester = new CommandTester($application->get(TrackexceptionCommand::NAME));

        $telemetryClientMock->expects($this->once())
            ->method('trackException')
            ->with(new \Exception(''));

        $telemetryClientMock->expects($this->once())
            ->method('flush')
            ->willReturn(new Response());

        $result = $tester->execute([]);

        $this->assertEquals($result, 0);
    }
}
