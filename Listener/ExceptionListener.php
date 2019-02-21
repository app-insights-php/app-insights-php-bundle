<?php

declare(strict_types=1);

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\Listener;

use AppInsightsPHP\Client\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ExceptionListener implements EventSubscriberInterface
{
    private $telemetryClient;
    private $exceptionLogged;

    public function __construct(Client $telemetryClient)
    {
        $this->telemetryClient = $telemetryClient;
        $this->exceptionLogged = false;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onException', 1000],
        ];
    }

    public function onException(GetResponseForExceptionEvent $event)
    {
        if ($this->exceptionLogged) {
            return;
        }

        $this->telemetryClient->trackException($event->getException());
        $this->exceptionLogged = true;
    }
}
