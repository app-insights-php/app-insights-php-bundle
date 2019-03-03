<?php

declare(strict_types=1);

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\DependencyInjection;

use AppInsightsPHP\Client\Client;
use AppInsightsPHP\Monolog\Handler\AppInsightsTraceHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class AppInsightsPHPExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('app_insights_php.xml');

        $container->setParameter('app_insights_php.instrumentation_key', $config['instrumentation_key']);
        $container->setParameter('app_insights_php.doctrine.track_dependency', $config['doctrine']['track_dependency']);

        // Make autowiring possible
        $container->setAlias(Client::class, 'app_insights_php.telemetry')->setPublic(true);

        $container->setDefinition('app_insights_php.configuration.exceptions',
            new Definition(\AppInsightsPHP\Client\Configuration\Exceptions::class, [
                $config['exceptions']['enabled'],
                (array) $config['exceptions']['ignored_exceptions'],
            ])
        );
        $container->setDefinition('app_insights_php.configuration.dependencies',
            new Definition(\AppInsightsPHP\Client\Configuration\Dependenies::class, [
                $config['dependencies']['enabled'],
            ])
        );
        $container->setDefinition('app_insights_php.configuration.requests',
            new Definition(\AppInsightsPHP\Client\Configuration\Requests::class, [
                $config['requests']['enabled'],
            ])
        );
        $container->setDefinition('app_insights_php.configuration.traces',
            new Definition(\AppInsightsPHP\Client\Configuration\Traces::class, [
                $config['traces']['enabled'],
            ])
        );
        $container->setDefinition('app_insights_php.configuration',
            new Definition(\AppInsightsPHP\Client\Configuration::class, [
                $config['enabled'],
                new Reference('app_insights_php.configuration.exceptions'),
                new Reference('app_insights_php.configuration.dependencies'),
                new Reference('app_insights_php.configuration.requests'),
                new Reference('app_insights_php.configuration.traces'),
            ])
        );

        $container->getDefinition('app_insights_php.telemetry.factory')->replaceArgument(1, new Reference('app_insights_php.configuration'));

        // Twig
        if (class_exists('Twig_Environment')) {
            $loader->load('app_insights_php_twig.xml');
        }

        // Doctrine
        if ($config['doctrine']['track_dependency']) {
            if (!class_exists('AppInsightsPHP\\Doctrine\\DBAL\\Logging\\DependencyLogger')) {
                throw new \RuntimeException('Please first run `composer require download app-insights-php/doctrine-dependency-logger` if you want to log DBAL queries.');
            }

            $loader->load('app_insights_php_doctrine.xml');
        }

        // Monolog
        if (\count($config['monolog']['handlers'])) {
            foreach ($config['monolog']['handlers'] as $name => $handlerConfig) {
                $id = sprintf(sprintf('app_insights_php.monolog.handler.%s', $name));

                switch ($handlerConfig['type']) {
                    case 'trace':
                        $class = AppInsightsTraceHandler::class;
                        $arguments = [
                            new Reference('app_insights_php.telemetry'),
                            $this->levelToMonologConst($handlerConfig['level']),
                            (bool) $handlerConfig['bubble'],
                        ];
                    break;
                    default:
                        throw new \RuntimeException('Unrecognized monolog handler type %s', $handlerConfig['type']);
                }

                $container->register($id, $class)
                    ->setArguments($arguments)
                    ->setPublic(false);
            }
        }
    }

    private function levelToMonologConst($level)
    {
        return \is_int($level) ? $level : \constant('Monolog\Logger::'.strtoupper($level));
    }
}
