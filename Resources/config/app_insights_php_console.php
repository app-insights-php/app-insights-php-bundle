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

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Command\TrackDependencyCommand;
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Command\TrackEventCommand;
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Command\TrackExceptionCommand;
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Command\TrackMetricCommand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();

    $services->set('app_insights_php.symfony.command.track_dependency', TrackDependencyCommand::class)
        ->tag('console.command', [])
        ->args([ref('app_insights_php.telemetry')]);

    $services->set('app_insights_php.symfony.command.track_metric', TrackMetricCommand::class)
        ->tag('console.command', [])
        ->args([ref('app_insights_php.telemetry')]);

    $services->set('app_insights_php.symfony.command.track_event', TrackEventCommand::class)
        ->tag('console.command', [])
        ->args([ref('app_insights_php.telemetry')]);

    $services->set('app_insights_php.symfony.command.track_exception', TrackExceptionCommand::class)
        ->tag('console.command', [])
        ->args([ref('app_insights_php.telemetry')]);
};
