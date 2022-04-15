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

use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Listener\ExceptionListener;
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Listener\HttpRequestListener;
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Listener\KernelTerminateListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();

    $services->set('app_insights_php.symfony.listener.http_request', HttpRequestListener::class)
        ->tag('kernel.event_subscriber', [])
        ->args([new Reference('app_insights_php.telemetry')]);

    $services->set('app_insights_php.symfony.listener.kernel_terminate', KernelTerminateListener::class)
        ->tag('kernel.event_subscriber', [])
        ->args([new Reference('app_insights_php.telemetry')]);

    $services->set('app_insights_php.symfony.listener.exception', ExceptionListener::class)
        ->tag('kernel.event_subscriber', [])
        ->args([new Reference('app_insights_php.telemetry'), []]);
};
