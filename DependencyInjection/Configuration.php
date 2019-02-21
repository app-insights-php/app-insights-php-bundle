<?php

declare(strict_types=1);

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('app_insights_php');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode') ? $treeBuilder->getRootNode() : $treeBuilder->root('app_insights_php');

        $rootNode
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('instrumentation_key')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('exceptions')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->arrayNode('ignored_exceptions')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('dependencies')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('traces')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('requests')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('doctrine')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('track_dependency')->defaultFalse()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
