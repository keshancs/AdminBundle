<?php

namespace AdminBundle\Twig;

use AdminBundle\Admin\AdminInterface;
use AdminBundle\Admin\Pool;
use AdminBundle\Admin\SettingManager;
use AdminBundle\Mapper\ListColumnDescriptor;
use AdminBundle\Routing\RouteLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AdminExtension extends AbstractExtension
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RouteLoader
     */
    private $routeLoader;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @var SettingManager
     */
    private $settingManager;

    /**
     * @param RouterInterface       $router
     * @param RouteLoader           $routeLoader
     * @param Pool                  $pool
     * @param ParameterBagInterface $parameterBag
     * @param Environment           $environment
     * @param PropertyAccessor      $propertyAccessor
     * @param SettingManager        $settingManager
     */
    public function __construct(
        RouterInterface       $router,
        RouteLoader           $routeLoader,
        Pool                  $pool,
        ParameterBagInterface $parameterBag,
        Environment           $environment,
        PropertyAccessor      $propertyAccessor,
        SettingManager        $settingManager
    ) {
        $this->router           = $router;
        $this->routeLoader      = $routeLoader;
        $this->pool             = $pool;
        $this->parameterBag     = $parameterBag;
        $this->environment      = $environment;
        $this->propertyAccessor = $propertyAccessor;
        $this->settingManager   = $settingManager;
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('parameter', [$this, 'getParameter']),
            new TwigFunction('get_admin_setting', [$this, 'getAdminSetting']),
            new TwigFunction('admin_pool', [$this, 'getAdminPool']),
            new TwigFunction('admin_route', [$this, 'getAdminRoute']),
            new TwigFunction('admin_path', [$this, 'getAdminPath']),
            new TwigFunction('render_list_element', [$this, 'renderListElement'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter(string $name)
    {
        return $this->parameterBag->get($name);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getAdminSetting(string $name)
    {
        return $this->settingManager->get($name);
    }

    /**
     * @return Pool
     */
    public function getAdminPool()
    {
        return $this->pool;
    }

    /**
     * @param string      $route
     * @param string|null $adminCode
     *
     * @return string|null
     */
    public function getAdminRoute(string $route, $adminCode = null)
    {
        if ($adminCode) {
            return $this->pool->getAdminByAdminCode($adminCode)->getRouteName($route);
        }

        return $this->routeLoader->getRoutePrefix() . '_' . $route;
    }

    /**
     * @param string      $path
     * @param string|null $adminCode
     * @param array       $parameters
     *
     * @return string|null
     */
    public function getAdminPath(string $path, $adminCode = null, array $parameters = [])
    {
        if ($adminRoute = $this->getAdminRoute($path, $adminCode)) {
            return $this->router->generate($adminRoute, $parameters);
        }

        return null;
    }

    /**
     * @param AdminInterface       $admin
     * @param ListColumnDescriptor $listColumnDescriptor
     * @param object               $data
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderListElement(AdminInterface $admin, ListColumnDescriptor $listColumnDescriptor, $data)
    {
        $options  = $listColumnDescriptor->getOptions();
        $template = sprintf('@Admin/CRUD/list_%s.html.twig', $options['widget'] ?? 'default');

        return $this->environment->render($template, [
            'admin'  => $admin,
            'column' => $listColumnDescriptor,
            'object' => $data,
            'value'  => $this->propertyAccessor->getValue($data, $listColumnDescriptor->getPropertyPath()),
        ]);
    }
}