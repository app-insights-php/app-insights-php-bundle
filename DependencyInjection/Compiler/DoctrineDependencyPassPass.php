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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class DoctrineDependencyPassPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->getParameter('app_insights_php.doctrine.track_dependency')) {
            return;
        }

        $doctrine = $container->getParameter('doctrine.connections');

        foreach ($doctrine as $connectionId) {
            $container
                ->getDefinition(sprintf('%s.configuration', $connectionId))
                ->addMethodCall('setSQLLogger', [new Reference('app_insights_php.doctrine.logger.dependency')]);
        }
    }
}
