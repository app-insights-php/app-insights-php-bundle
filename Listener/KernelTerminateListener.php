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

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\Listener;

use AppInsightsPHP\Client\Client;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class KernelTerminateListener implements EventSubscriberInterface
{
    private $telemetryClient;

    public function __construct(Client $telemetryClient)
    {
        $this->telemetryClient = $telemetryClient;
    }

    public static function getSubscribedEvents()
    {
        $listeners = [
            KernelEvents::TERMINATE => ['onTerminate', -1000],
        ];

        if (class_exists('Symfony\Component\Console\ConsoleEvents')) {
            $listeners[ConsoleEvents::TERMINATE] = 'onTerminate';
        }

        return $listeners;
    }

    public function onTerminate()
    {
        if (!$this->telemetryClient->getContext()->getInstrumentationKey()) {
            // instrumentation key is empty
            return;
        }

        if (!\count($this->telemetryClient->getChannel()->getQueue())) {
            // telemetry client queue is empty
            return;
        }

        $this->telemetryClient->flush();
    }
}
