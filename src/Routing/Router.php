<?php

namespace AdminBundle\Routing;

use AdminBundle\Controller\CmsController;
use AdminBundle\Controller\SecurityController;
use AdminBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final class Router implements RouterInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

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
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em              = $em;
        $this->routeCollection = new RouteCollection();

        foreach ($this->routes as $name => $route) {
            list ($controller, $action) = $route['controller'];

            $name     = $this->routePrefix . '_' . $name;
            $path     = '/' . $this->routePrefix . rtrim($route['path'], '/');
            $defaults = ['_controller' => $controller . '::' . $action];

            $this->routeCollection->add($name, new Route($path, $defaults));
        }
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
        return $this->routeCollection;
    }

    /**
     * @return UrlGenerator|UrlGeneratorInterface
     */
    public function getGenerator()
    {
        if (null == $this->urlGenerator) {
            $this->urlGenerator = new UrlGenerator($this->routeCollection, $this->context);
        }

        return $this->urlGenerator;
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
}