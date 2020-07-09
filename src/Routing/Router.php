<?php

namespace AdminBundle\Routing;

use AdminBundle\Admin\Pool;
use AdminBundle\Controller\CmsController;
use AdminBundle\Controller\SecurityController;
use AdminBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final class Router extends Loader implements RouterInterface
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var RequestContext
     */
    private $context;

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var string
     */
    private $routePrefix = 'admin';

    /**
     * @var string[]
     */
    private $routes = [
        'index' => [
            'path'       => '/',
            'controller' => [CmsController::class, 'index'],
        ],
        'dashboard' => [
            'path'       => '/dashboard',
            'controller' => [CmsController::class, 'dashboard'],
        ],
        'login' => [
            'path'       => '/login',
            'controller' => [SecurityController::class, 'login'],
        ],
        'logout' => [
            'path'       => '/logout',
            'controller' => [SecurityController::class, 'logout'],
        ],
    ];

    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @param Pool                   $pool
     * @param EntityManagerInterface $em
     * @param LoggerInterface        $logger
     */
    public function __construct(Pool $pool, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->pool   = $pool;
        $this->em     = $em;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @inheritDoc
     */
    public function getRouteCollection()
    {
        if (false === $this->loaded) {
            $this->routeCollection = $this->loadRouteCollection();
        }

        return $this->routeCollection;
    }

    /**
     * @inheritDoc
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        return $this->getGenerator()->generate($name, $parameters, $referenceType);
    }

    /**
     * @inheritDoc
     */
    public function match($pathinfo)
    {
        if ($page = $this->em->getRepository(Page::class)->findOneBy(['path' => $pathinfo])) {
            return [
                '_route'      => 'cms_page',
                '_controller' => CmsController::class . '::page',
                '_page'       => $page,
                '_locale'     => $page->getLocale(),
            ];
        }

        throw new ResourceNotFoundException();
    }

    /**
     * @inheritDoc
     */
    public function load($resource, $type = null)
    {
        return $this->getRouteCollection();
    }

    /**
     * @inheritDoc
     */
    public function supports($resource, $type = null)
    {
        return true;
    }

    /**
     * @return UrlGenerator|UrlGeneratorInterface
     */
    public function getGenerator()
    {
        if (null == $this->urlGenerator) {
            $this->urlGenerator = new UrlGenerator($this->getRouteCollection(), $this->context, $this->logger);
        }

        return $this->urlGenerator;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getRouteName(string $name)
    {
        return $this->routePrefix . '_' . $name;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getRoutePath(string $path)
    {
        return '/' . $this->routePrefix . rtrim($path, '/');
    }

    /**
     * @return RouteCollection
     */
    private function loadRouteCollection()
    {
        $routeCollection = new RouteCollection();

        foreach ($this->routes as $name => $route) {
            list ($controller, $action) = $route['controller'];

            $name     = $this->routePrefix . '_' . $name;
            $path     = '/' . $this->routePrefix . rtrim($route['path'], '/');
            $defaults = ['_controller' => $controller . '::' . $action];

            $routeCollection->add($name, new Route($path, $defaults));
        }

        foreach ($this->pool->getServices() as $code => $admin) {
            $name       = $admin->getRouteName('list');
            $path       = $admin->getRoutePath('list');
            $defaults   = ['_controller' => ($admin->getController() ?: CmsController::class) . '::list'];

            $routeCollection->add($name, new Route($path, $defaults));
        }

        $this->loaded = true;

        return $routeCollection;
    }
}