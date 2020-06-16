<?php

namespace AdminBundle\Twig;

use AdminBundle\Admin\Pool;
use AdminBundle\Route\RouteGenerator;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AdminExtension extends AbstractExtension
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RouteGenerator
     */
    private $routeGenerator;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @param RouterInterface $router
     * @param RouteGenerator  $routeGenerator
     * @param Pool            $pool
     */
    public function __construct(
        RouterInterface $router,
        RouteGenerator  $routeGenerator,
        Pool            $pool
    ) {
        $this->router         = $router;
        $this->routeGenerator = $routeGenerator;
        $this->pool           = $pool;
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('admin_pool', [$this, 'getAdminPool']),
            new TwigFunction('admin_route', [$this, 'getAdminRoute']),
            new TwigFunction('admin_path', [$this, 'getAdminPath']),
        ];
    }

    /**
     * @return Pool
     */
    public function getAdminPool()
    {
        return $this->pool;
    }

    /**
     * @param string      $path
     * @param string|null $adminCode
     *
     * @return string
     */
    public function getAdminRoute(string $path, $adminCode = null)
    {
        if ($adminCode) {
            $route = $this->pool->getAdminByAdminCode($adminCode)->getRoute($path);
        } else {
            $route = $this->routeGenerator->getRouteName($path);
        }

        return $route;
    }

    /**
     * @param string      $path
     * @param string|null $adminCode
     * @param array       $parameters
     *
     * @return string
     */
    public function getAdminPath(string $path, $adminCode = null, array $parameters = [])
    {
        return $this->router->generate($this->getAdminRoute($path, $adminCode), $parameters);
    }
}