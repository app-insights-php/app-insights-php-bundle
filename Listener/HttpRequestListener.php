<?php

declare(strict_types=1);

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\Listener;

use AppInsightsPHP\Client\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class HttpRequestListener implements EventSubscriberInterface
{
    private $telemetryClient;

    private $request;
    private $requestStartTime;
    private $requestStartTimeMs;

    public function __construct(Client $telemetryClient)
    {
        $this->telemetryClient = $telemetryClient;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1000],
            KernelEvents::RESPONSE => ['onKernelResponse', -1000],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->requestStartTime = time();
        $this->requestStartTimeMs = (int) round(microtime(true) * 1000, 1);

        $request = $event->getRequest();

        $this->telemetryClient->getContext()->getLocationContext()->setIp($request->getClientIp());

        if ($request->hasSession()) {
            $this->telemetryClient->getContext()->getSessionContext()->setId($request->getSession()->getId());
        }

        $this->telemetryClient->getContext()->getOperationContext()->setName($request->getMethod().' '.$request->getPathInfo());

        $this->request = $this->telemetryClient->beginRequest(
            $request->getMethod().' '.$request->getPathInfo(),
            $request->getUriForPath($request->getPathInfo()),
            $this->requestStartTime
        );
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();

        $this->telemetryClient->endRequest(
            $this->request,
            (int) round(microtime(true) * 1000, 1) - $this->requestStartTimeMs,
            $response->getStatusCode(),
            $response->isSuccessful()
        );
    }
}
