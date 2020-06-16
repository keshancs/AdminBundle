<?php

namespace AdminBundle;

use AdminBundle\Admin\AdminInterface;
use AdminBundle\Controller\AdminController;
use AdminBundle\Controller\SecurityController;
use AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class AdminBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function boot()
    {
        $routeCollection = new RouteCollection();
        $pool            = $this->container->get('admin.pool');
        $routeGenerator  = $this->container->get('admin.route_generator');

        foreach ($pool->getServices() as $adminCode) {
            $adminRoutes = [];
            /** @var AdminInterface $admin */
            $admin       = $pool->getAdminByAdminCode($adminCode);
            $name        = $admin->getName();

            foreach ($routeGenerator->getRoutes('CRUD') as $crudRoute) {
                $pathParts    = [$name];
                $requirements = [];

                if (in_array($crudRoute, $routeGenerator->getRoutes('id'))) {
                    $pathParts[] = '{id}';

                    $requirements['id'] = '\d+';
                }

                $pathParts[]             = $crudRoute;
                $adminRoutes[$crudRoute] = $routeGenerator->getRouteName($name . '_' . $crudRoute);

                $routeCollection->add(
                    $adminRoutes[$crudRoute],
                    new Route($routeGenerator->buildPath($pathParts), [
                        '_controller' => $admin->getController() . '::' . $crudRoute,
                        '_admin'      => $adminCode,
                    ], $requirements)
                );
            }

            $admin->setRoutes($adminRoutes);
        }

        foreach ($routeGenerator->getRoutes('security') as $route) {
            $routeName = $routeGenerator->getRouteName($route);

            $routeCollection->add(
                $routeName,
                new Route(
                    $routeGenerator->buildPath([$route]),
                    ['_controller' => SecurityController::class . '::' . $route]
                )
            );
        }

        foreach (['index', 'dashboard'] as $defaultRoute) {
            $routeCollection->add(
                $routeGenerator->getRouteName($defaultRoute),
                new Route(
                    $routeGenerator->buildPath($defaultRoute === 'index' ? [] : [$defaultRoute]),
                    ['_controller' => AdminController::class . '::' . $defaultRoute]
                )
            );
        }

        $this->container->get('router')->getRouteCollection()->addCollection($routeCollection);
    }

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddDependencyCallsCompilerPass());
    }
}