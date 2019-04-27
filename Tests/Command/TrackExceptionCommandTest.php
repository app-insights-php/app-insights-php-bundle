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
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Command\TrackExceptionCommand;
use ApplicationInsights\Telemetry_Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class TrackExceptionCommandTest extends TestCase
{
    public function test_tracking_metric()
    {
        $client = new Client($telemetryClientMock = $this->createMock(Telemetry_Client::class), Configuration::createDefault());

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
