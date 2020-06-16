<?php

namespace AdminBundle\Route;

use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class RouteGenerator implements UrlGeneratorInterface
{
    /**
     * @var string
     */
    private $pathPrefix;

    /**
     * @var string
     */
    private $routePrefix;

    /**
     * @var string[]
     */
    private $routes = [
        'CRUD'     => ['create', 'list', 'edit', 'update', 'delete'],
        'id'       => ['edit', 'update', 'delete'],
        'security' => ['login', 'logout'],
    ];

    /**
     * @param string $pathPrefix
     * @param string $routePrefix
     */
    public function __construct(string $pathPrefix, string $routePrefix)
    {
        $this->pathPrefix  = $pathPrefix;
        $this->routePrefix = $routePrefix;
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
     * @return string
     */
    public function getPathPrefix()
    {
        return $this->pathPrefix;
    }

    /**
     * @return string
     */
    public function getRoutePrefix()
    {
        return $this->routePrefix;
    }

    /**
     * @param array $pathParts
     *
     * @return string
     */
    public function buildPath(array $pathParts)
    {
        array_unshift($pathParts, $this->pathPrefix);

        return implode('/', $pathParts);
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getRoutes(string $type)
    {
        return $this->routes[$type] ?? [];
    }

    public function setContext(RequestContext $context)
    {
    }

    public function getContext()
    {
    }

    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
    }
}