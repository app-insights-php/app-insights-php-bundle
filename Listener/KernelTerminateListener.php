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
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class KernelTerminateListener implements EventSubscriberInterface
{
    public const CACHE_CHANNEL_KEY = 'app_insights_php.failure_cache';
    public const CACHE_CHANNEL_TTL_SEC = 86400; // 1 day

    private $telemetryClient;
    private $logger;
    private $failureCache;

    public function __construct(Client $telemetryClient, CacheInterface $failureCache = null, LoggerInterface $logger = null)
    {
        $this->telemetryClient = $telemetryClient;
        $this->logger = $logger;
        $this->failureCache = $failureCache;
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
        if (!\count($this->telemetryClient->getChannel()->getQueue())) {
            // telemetry client queue is empty
            return ;
        }

        try {
            if ($this->failureCache && $this->failureCache->has(self::CACHE_CHANNEL_KEY)) {
                $queueContent = unserialize($this->failureCache->get(self::CACHE_CHANNEL_KEY));

                $queueContent = array_merge($queueContent, $this->telemetryClient->getChannel()->getQueue());

                $this->telemetryClient->getChannel()->setQueue($queueContent);

                $this->failureCache->delete(self::CACHE_CHANNEL_KEY);
            }

            $this->telemetryClient->flush();
        } catch (\Throwable $e) {

            if ($this->failureCache) {
                $queueContent = $this->telemetryClient->getChannel()->getQueue();

                if ($this->failureCache->has(self::CACHE_CHANNEL_KEY)) {
                    $previousQueueContent = \unserialize($this->failureCache->get(self::CACHE_CHANNEL_KEY));

                    $queueContent = array_merge($previousQueueContent, $queueContent);
                }

                $this->failureCache->set(self::CACHE_CHANNEL_KEY, \serialize($queueContent), self:: CACHE_CHANNEL_TTL_SEC);
            }

            if ($this->logger) {
                $this->logger->error(
                    sprintf('Exception occurred while flushing App Insights Telemetry Client: %s', $e->getMessage()),
                    json_decode($this->telemetryClient->getChannel()->getSerializedQueue(), true)
                );
            }
        }
    }
}
