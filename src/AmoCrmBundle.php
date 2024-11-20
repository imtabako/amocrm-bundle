<?php

declare(strict_types=1);

namespace Ectool\AmoCrmBundle;

use Ectool\AmoCrmBundle\DependencyInjection\Compiler\AmoCrmApiCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class AmoCrmBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new AmoCrmApiCompilerPass())
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/api.xml');
    }
}
