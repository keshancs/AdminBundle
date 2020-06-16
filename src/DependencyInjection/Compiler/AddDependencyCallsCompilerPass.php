<?php

namespace AdminBundle\DependencyInjection\Compiler;

use AdminBundle\Controller\AdminController;
use AdminBundle\Route\RouteGenerator;
use AdminBundle\Twig\AdminExtension;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddDependencyCallsCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $services = [];

        foreach ($container->findTaggedServiceIds('admin') as $id => $data) {
            $admin = $container->getDefinition($id);
            $admin->setMethodCalls([
                ['setPool', [new Reference('admin.pool')]],
                ['setRouter', [new Reference('router')]],
                ['setFormFactory', [new Reference('form.factory')]],
                ['setEntityManager', [new Reference('doctrine.orm.entity_manager')]],
                ['setTranslator', [new Reference('translator')]],
                ['setRouteGenerator', [new Reference('admin.route_generator')]],
            ]);

            $services[] = $admin->getArgument(0);
        }

        $pool = $container->getDefinition('admin.pool');
        $pool->addMethodCall('setServices', [$services]);

        $twig = $container->getDefinition(AdminExtension::class);
        $twig->addArgument(new Reference('router'));
        $twig->addArgument(new Reference('admin.route_generator'));
        $twig->addArgument(new Reference('admin.pool'));
    }
}