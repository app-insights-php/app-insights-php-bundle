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
use AppInsightsPHP\Client\Client;
use AppInsightsPHP\Client\ClientFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();

    $services->set('app_insights_php.telemetry.factory', ClientFactory::class)
        ->args(['%app_insights_php.instrumentation_key%', '', '', '']);

    $services->set('app_insights_php.telemetry', Client::class)
        ->public()
        ->factory([ref('app_insights_php.telemetry.factory'), 'create']);
};
