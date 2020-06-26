<?php

namespace AdminBundle\DependencyInjection\Compiler;

use AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class AddDependencyCallsCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $services = [];

        foreach ($container->findTaggedServiceIds('admin') as $id => $data) {
            $admin = $container->getDefinition($id);

            if (!in_array(AbstractAdmin::class, class_parents($admin->getClass()))) {
                throw new \RuntimeException(
                    sprintf('Admin class `%s` must extend `%s` class', $admin->getClass(), AbstractAdmin::class)
                );
            }

            $admin->setMethodCalls([
                ['setPool', [new Reference('admin.pool')]],
                ['setRouter', [new Reference('router')]],
                ['setFormFactory', [new Reference('form.factory')]],
                ['setEntityManager', [new Reference('doctrine.orm.entity_manager')]],
                ['setTranslator', [new Reference('translator')]],
                ['setSettingManager', [new Reference('admin.setting_manager')]],
                ['setTemplate', ['list', '@Admin/CRUD/list.html.twig']],
                ['setTemplate', ['create', '@Admin/CRUD/create.html.twig']],
                ['setTemplate', ['edit', '@Admin/CRUD/edit.html.twig']],
            ]);

            $code = $admin->getArgument(0);

            $services[$code] = new Reference($id);
        }

        $pool = $container->getDefinition('admin.pool');
        $pool->addMethodCall('setServices', [$services]);
    }
}