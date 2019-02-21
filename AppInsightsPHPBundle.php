<?php

declare(strict_types=1);

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle;

use AppInsightsPHP\Symfony\AppInsightsPHPBundle\DependencyInjection\Compiler\DoctrineDependencyPassPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class AppInsightsPHPBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DoctrineDependencyPassPass());
    }
}
