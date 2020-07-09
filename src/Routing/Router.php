<?php

namespace AdminBundle\Routing;

use AdminBundle\Admin\Pool;
use AdminBundle\Controller\CmsController;
use AdminBundle\Controller\SecurityController;
use AdminBundle\Entity\Page;
use AdminBundle\Utils\TranslationUtils;
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
        return $this->loaded ? $this->routeCollection : $this->loadRouteCollection();
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
     * @param string      $name
     * @param string      $path
     * @param array       $defaults
     * @param array       $requirements
     * @param array       $options
     * @param string|null $host
     * @param array       $schemes
     * @param array       $methods
     * @param string|null $condition
     *
     * @return Router
     */
    public function add(
        string $name,
        string $path,
        array $defaults = [],
        array $requirements = [],
        array $options = [],
        ?string $host = '',
        $schemes = [],
        $methods = [],
        ?string $condition = ''
    ) {
        if ($admin = $this->context->getParameter('_admin')) {
            $actionName = TranslationUtils::snakeCaseToCamelCase($name);
            $path       = $admin->getRoutePath($path);
            $name       = $admin->getRouteName($name);

            $defaults['_admin']      = $admin->getCode();
            $defaults['_action']     = $actionName;
            $defaults['_controller'] = $defaults['_controller'] ??
                ($admin->getController() ?: CmsController::class) . '::' . $actionName;
        } else {
            $path = $this->getRoutePath($path);
            $name = $this->getRouteName($name);
        }

        $this->routeCollection->add(
            $name,
            new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition)
        );

        return $this;
    }

    /**
     * @return UrlGenerator|UrlGeneratorInterface
     */
    private function getGenerator()
    {
        if (null == $this->urlGenerator) {
            $this->urlGenerator = new UrlGenerator($this->getRouteCollection(), $this->context, $this->logger);
        }

        return $this->urlGenerator;
    }

    /**
     * @return RouteCollection
     */
    private function loadRouteCollection()
    {
        $this->routeCollection = new RouteCollection();

        $routes = [
            'index'     => ['/',          'index'],
            'dashboard' => ['/dashboard', 'dashboard'],
            'login'     => ['/login',     'login',  SecurityController::class],
            'logout'    => ['/logout',    'logout', SecurityController::class],
        ];

        foreach ($routes as $name => $route) {
            list ($path, $action) = $route;

            $defaults = [
                '_controller' => ($route[2] ?? CmsController::class) . '::' . $action,
                '_action'     => $action,
            ];

            $this->add($name, $path, $defaults);
        }

        $routes = [
            'list'      => ['list',           []],
            'create'    => ['create',         []],
            'edit'      => ['{id}/edit',      ['id' => '\d+']],
            'update'    => ['{id}/update',    ['id' => '\d+']],
            'translate' => ['{id}/translate', ['id' => '\d+']],
            'delete'    => ['{id}/delete',    ['id' => '\d+']],
        ];

        foreach ($this->pool->getServices() as $code => $admin) {
            $this->context->setParameter('_admin', $admin);

            foreach ($routes as $name => $route) {
                list($path, $requirements) = $route;

                $this->add($name, $path, [], $requirements);
            }

            $admin->configureRoutes($this);

            $this->context->setParameter('_admin', null);
        }

        $this->loaded = true;

        return $this->routeCollection;
    }
}