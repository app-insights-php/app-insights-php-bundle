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

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\Tests\Functional;

use AppInsightsPHP\Symfony\AppInsightsPHPBundle\AppInsightsPHPBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class AppKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new MonologBundle(),
            new AppInsightsPHPBundle(),
        ];
    }

    public function getCacheDir()
    {
        return \sys_get_temp_dir() . '/PHPAppInsights/cache';
    }

    public function getLogDir()
    {
        return \sys_get_temp_dir() . '/PHPAppInsights/logs';
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader) : void
    {
        $configFilePath = __DIR__ . '/config/app_insights_php.php';

        if (!\file_exists($configFilePath)) {
            throw new \RuntimeException('Please create ' . $configFilePath . ' first, use ' . $configFilePath . '.dist as a template');
        }

        $c->loadFromExtension('framework', [
            'secret' => 'S0ME_SECRET',
        ]);
        $c->loadFromExtension('app_insights_php', require_once $configFilePath);
        $c->loadFromExtension('monolog', [
            'handlers' => [
                'file_log' => [
                    'type' => 'stream',
                    'path' => '%kernel.logs_dir%/%kernel.environment%.log',
                    'level' => 'error',
                ],
                'console' => [
                    'type' => 'console',
                    'process_psr_3_messages' => false,
                    'channels' => ['!event', '!doctrine', '!console'],
                ],
            ],
        ]);
    }

    protected function configureRoutes(RouteCollectionBuilder $routes) : void
    {
    }
}
