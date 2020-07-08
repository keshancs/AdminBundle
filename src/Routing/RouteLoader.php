<?php

namespace AdminBundle\Routing;

use AdminBundle\Admin\AdminInterface;
use AdminBundle\Admin\Pool;
use AdminBundle\Controller\CmsController;
use AdminBundle\Controller\RobotsController;
use AdminBundle\Controller\SecurityController;
use AdminBundle\Controller\SitemapController;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

final class RouteLoader extends Loader
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var RouteCollection
     */
    private $collection;

    /**
     * @var AdminInterface|null
     */
    private $admin;

    /**
     * @var bool
     */
    private $loading = false;

    /**
     * @param Pool   $pool
     * @param Router $router
     */
    public function __construct(Pool $pool)
    {
        $this->pool       = $pool;
        $this->collection = new RouteCollection();
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

        $requirements = ['id' => '\d+'];

        $routes = [
            'list'           => ['list',      []],
            'create'         => ['create',    []],
            '{id}/edit'      => ['edit',      $requirements],
            '{id}/update'    => ['update',    $requirements],
            '{id}/translate' => ['translate', $requirements],
            '{id}/delete'    => ['delete',    $requirements],
        ];

        /** @var AdminInterface $admin */
        foreach ($this->pool->getServices() as $code => $admin) {
            $this->admin = $admin;

            foreach ($routes as $path => $data) {
                list ($name, $requirements) = $data;

                $defaults = [
                    '_controller' => CmsController::class . '::' . $name,
                    '_admin'      => $admin->getCode()
                ];

                $this->add($name, $path, $defaults, $requirements);
            }

            $admin->configureRoutes($this);
        }

        $this->admin = null;

        $this
            ->add('index', '')
            ->add('dashboard')
            ->add('login', 'login', ['_controller' => SecurityController::class . '::login'])
            ->add('logout', 'logout', ['_controller' => SecurityController::class . '::logout'])
            ->add('sitemap', '/sitemap.xml', ['_controller' => SitemapController::class . '::index'])
            ->add('robots', '/robots.txt', ['_controller' => RobotsController::class . '::index'])
        ;

        $this->loading = false;

        return $this->collection;
    }

    /**
     * @param string      $name
     * @param string|null $path
     * @param array       $defaults
     * @param array       $requirements
     * @param array       $options
     * @param string|null $host
     * @param array       $schemes
     * @param array       $methods
     * @param string|null $condition
     *
     * @return RouteLoader
     */
    public function add(
        string $name,
        ?string $path = null,
        array $defaults = [],
        array $requirements = [],
        array $options = [],
        ?string $host = '',
        $schemes = [],
        $methods = [],
        ?string $condition = ''
    ) {
//        $controller = CmsController::class;
//        $method     = TranslationUtils::snakeCaseToCamelCase($name);
//        $path       = null === $path ? $name : $path;
//
//        if ($this->admin instanceof AdminInterface) {
//            $name       = $this->admin->getRouteName($name);
//            $path       = $this->admin->getRoutePath($path);
//            $controller = $this->admin->getController();
//        } else {
//            $name = $this->router->getRouteName($name);
//            $path = $this->router->getRoutePath($path);
//        }
//
//        $defaults['_controller'] = $defaults['_controller'] ?? $controller . '::' . $method;
//
//        $this->collection->add(
//            $name,
//            new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition)
//        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function supports($resource, $type = null)
    {
        return $resource === 'admin.route_loader';
    }
}
