<?php

namespace AdminBundle\Routing;

use AdminBundle\Admin\AdminInterface;
use AdminBundle\Admin\Pool;
use AdminBundle\Controller\CmsController;
use AdminBundle\Controller\SecurityController;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class RouteLoader extends Loader
{
    /**
     * @var bool
     */
    private $loading = false;

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
     * @param string $name
     * @param array  $config
     *
     * @return string
     */
    private function getRouteController(string $name, array $config)
    {
        $controller = $config['security'] ? SecurityController::class : CmsController::class;

        return $controller . '::' . $name;
    }

    /**
     * @inheritDoc
     */
    public function load($resource, $type = null)
    {
        if ($this->loading) {
            throw new LoaderLoadException($resource, null, null, null, $type);
        }

        $this->loading = true;

        $collection = new RouteCollection();

        foreach ($this->getRoutes() as $name => $config) {
            if ($config['crud']) {
                $this->loadCRUD($collection, $name, $config['id']);

                continue;
            }

            $route = new Route('/' . $this->routePrefix . '/' . $name);
            $route->setDefault('_controller', $this->getRouteController($name, $config));

            $collection->add($this->routePrefix . '_' . $name, $route);
        }

        $this->loading = false;

        return $collection;
    }

    /**
     * @param RouteCollection $collection
     * @param string          $name
     * @param bool            $id
     */
    private function loadCRUD(RouteCollection $collection, string $name, bool $id)
    {
        /** @var AdminInterface $admin */
        foreach ($this->pool->getServices() as $code => $admin) {
            $route = new Route('/' . $this->routePrefix . '/' . $admin->getName());

            if ($id) {
                $route
                    ->setPath($route->getPath() . '/{id}')
                    ->setRequirement('id', '\d+')
                ;
            }

            $route
                ->setPath($route->getPath() . '/' . $name)
                ->setDefaults([
                    '_controller' => sprintf('%s::%s', $admin->getController(), $name),
                    '_admin'      => $code,
                ])
            ;

            $collection->add($admin->getRouteName($name), $route);
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