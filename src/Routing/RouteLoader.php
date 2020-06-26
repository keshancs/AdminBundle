<?php

namespace AdminBundle\Routing;

use AdminBundle\Admin\AdminInterface;
use AdminBundle\Admin\Pool;
use AdminBundle\Controller\CmsController;
use AdminBundle\Controller\SecurityController;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class RouteLoader extends Loader
{
    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var array[]
     */
    private $routes = [
        'dashboard' => [],
        'login'     => ['security' => true],
        'logout'    => ['security' => true],
        'list'      => ['crud' => true],
        'create'    => ['crud' => true],
        'edit'      => ['id' => true, 'crud' => true],
        'update'    => ['id' => true, 'crud' => true],
        'translate' => ['id' => true, 'crud' => true],
    ];

    /**
     * @var bool[]
     */
    private $routeDefaultConfig = [
        'id'       => false,
        'crud'     => false,
        'security' => false,
    ];

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var string
     */
    private $routePrefix = 'admin';

    /**
     * @param Pool $pool
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @inheritDoc
     */
    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();

        if ($this->loaded) {
            return $collection;
        }

        foreach ($this->getRoutes() as $route => $config) {
            $requirements = [];
            $routeName    = $routePath = [$route];

            if ($config['crud']) {
                $this->loadCRUD($collection, $route, $config['id']);

                continue;
            }

            $controller = $config['security'] ? SecurityController::class : CmsController::class;
            $defaults   = ['_controller' => $controller . '::' . $route];

            array_unshift($routeName, $this->routePrefix);
            array_unshift($routePath, '', $this->routePrefix);

            $routeName = implode('_', $routeName);
            $routePath = implode('/', $routePath);

            $collection->add($routeName, new Route($routePath, $defaults, $requirements));
        }

        $this->loaded = true;

        return $collection;
    }

    /**
     * @param RouteCollection $collection
     * @param string          $route
     * @param bool            $id
     */
    private function loadCRUD(RouteCollection $collection, string $route, bool $id)
    {
        /** @var AdminInterface $admin */
        foreach ($this->pool->getServices() as $code => $admin) {
            $routePath    = [$route];
            $requirements = [];
            $defaults     = [
                '_controller' => sprintf('%s::%s', $admin->getController(), $route),
                '_admin'      => $code,
            ];

            if ($id) {
                array_unshift($routePath, '{id}');

                $requirements['id'] = '\d+';
            }

            array_unshift($routePath, '', $this->routePrefix, $admin->getName());

            $routePath  = implode('/', $routePath);

            $collection->add(
                $admin->getRouteName($route),
                new Route($routePath, $defaults, $requirements)
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($resource, $type = null)
    {
        return $type === 'admin';
    }

    /**
     * @return array[]
     */
    public function getRoutes()
    {
        $callback = function ($route) {
            return array_merge($this->routeDefaultConfig, $route);
        };

        return array_map($callback, $this->routes);
    }

    /**
     * @return string
     */
    public function getRoutePrefix()
    {
        return $this->routePrefix;
    }
}