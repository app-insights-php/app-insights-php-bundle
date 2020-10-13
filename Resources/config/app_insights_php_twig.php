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

use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Twig\TelemetryExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('app_insights_php.twig.telemetry', TelemetryExtension::class)
        ->tag('twig.extension', [])
        ->args([ref('app_insights_php.telemetry')]);
};
