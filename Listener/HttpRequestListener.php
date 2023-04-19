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
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\FlatArray;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
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

    public function onKernelRequest(RequestEvent $event) : void
    {
        if (!$this->isMainRequest($event)) {
            return;
        }

        if (!$this->telemetryClient->configuration()->requests()->isEnabled()) {
            return;
        }

        if (!$this->telemetryClient->getContext()->getInstrumentationKey()) {
            // instrumentation key is emtpy
            return;
        }

        $this->requestStartTime = \time();
        $this->requestStartTimeMs = (int) \round(\microtime(true) * 1000, 1);

        $request = $event->getRequest();

        $this->telemetryClient->getContext()->getLocationContext()->setIp($request->getClientIp());

        if ($request->hasSession()) {
            $this->telemetryClient->getContext()->getSessionContext()->setId($request->getSession()->getId());
        }

        $this->telemetryClient->getContext()->getOperationContext()->setName($request->getMethod() . ' ' . $request->getPathInfo());

        $this->request = $this->telemetryClient->beginRequest(
            $request->getMethod() . ' ' . $request->getPathInfo(),
            $request->getUriForPath($request->getPathInfo()),
            $this->requestStartTime
        );
    }

    public function onKernelResponse(ResponseEvent $event) : void
    {
        if (!$this->isMainRequest($event)) {
            return;
        }

        if (!$this->telemetryClient->configuration()->requests()->isEnabled()) {
            return;
        }

        if (!$this->telemetryClient->getContext()->getInstrumentationKey()) {
            // instrumentation key is empty
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        $this->telemetryClient->endRequest(
            $this->request,
            (int) \round(\microtime(true) * 1000, 1) - $this->requestStartTimeMs,
            $response->getStatusCode(),
            $response->isSuccessful() || $response->isRedirection(),
            (new FlatArray([
                'headers' => [
                    'accept-language' => $request->headers->get('accept-language'),
                    'accept-encoding' => $request->headers->get('accept-encoding'),
                    'accept' => $request->headers->get('accept'),
                    'user-agent' => $request->headers->get('user-agent'),
                    'host' => $request->headers->get('host'),
                ],
                'query' => $request->query->all(),
                'clientIps' => $request->getClientIps(),
            ]))()
        );
    }

    private function isMainRequest(KernelEvent $event) : bool
    {
        return \method_exists($event, 'isMainRequest')
            ? $event->isMainRequest()
            : $event->isMasterRequest();
    }
}
