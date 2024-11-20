<?php

declare(strict_types=1);

namespace Ectool\AmoCrmBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AmoCrmApiCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('amocrm.api_registry')) {
            return;
        }

        $definition = $container->findDefinition('amocrm.api_registry');

        $taggedServices = $container->findTaggedServiceIds('amocrm.api');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('addApi', [
                    new Reference($id),
                    $attributes['alias'],
                ]);
            }
        }
    }
}
