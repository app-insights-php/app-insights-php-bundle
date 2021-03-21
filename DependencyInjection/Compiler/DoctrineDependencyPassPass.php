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

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\DependencyInjection\Compiler;

use Doctrine\DBAL\Logging\LoggerChain;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class DoctrineDependencyPassPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) : void
    {
        if (false === $container->getParameter('app_insights_php.doctrine.track_dependency')) {
            return;
        }

        $doctrine = $container->getParameter('doctrine.connections');
        $extendedLoggerChains = [];

        $appInsightsLoggerRef = new Reference('app_insights_php.doctrine.logger.dependency');

        foreach ($doctrine as $connectionKey => $connectionId) {
            $definition = $container->getDefinition(\sprintf('%s.configuration', $connectionId));

            $setSqlLoggerCall = $this->getSetSQLLoggerCall($definition);

            if (null === $setSqlLoggerCall) {
                $definition->addMethodCall('setSQLLogger', [$appInsightsLoggerRef]);

                continue;
            }

            $logger = (string) $setSqlLoggerCall[1][0];

            if (\in_array($logger, $extendedLoggerChains, true)) {
                break;
            }
            $loggerDefinition = $container->getDefinition($logger);

            if (LoggerChain::class === $loggerDefinition->getClass()) {
                $argument = $loggerDefinition->getArgument(0) ?? [];

                if (!\is_array($argument)) {
                    $argument = \iterator_to_array($argument);
                }
                $argument[] = $appInsightsLoggerRef;
                $loggerDefinition->setArgument(0, $argument);
            } else {
                $loggerChainName = 'doctrine.dbal.logger.chain.' . $connectionKey;
                $loggerChain = $container->hasDefinition($loggerChainName)
                    ? $container->getDefinition($loggerChainName)
                    : $container->register($loggerChainName, LoggerChain::class);
                $loggerChain->setArgument(0, [
                    $setSqlLoggerCall[1][0],
                    $appInsightsLoggerRef,
                ]);
                $definition->removeMethodCall('setSQLLogger')
                    ->addMethodCall('setSQLLogger', [new Reference($loggerChainName)]);
                $extendedLoggerChains[] = $loggerChainName;
            }
        }
    }

    private function getSetSQLLoggerCall(Definition $definition) : ?array
    {
        $method = null;

        foreach ($definition->getMethodCalls() as $methodCall) {
            if ('setSQLLogger' === $methodCall[0]) {
                $method = $methodCall;
            }
        }

        return $method;
    }
}
