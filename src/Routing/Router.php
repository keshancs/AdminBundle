<?php

namespace AdminBundle\Routing;

use AdminBundle\Controller\CmsController;
use AdminBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Config\Resource\FileExistenceResource;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Config\ContainerParametersResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final class Router implements RouterInterface
{
    /**
     * @var RouterInterface $router
     */
    private $router;

    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    /**
     * @var ObjectRepository $pageRepo
     */
    private $pageRepo;

    /**
     * @var RequestStack $requestStack
     */
    private $requestStack;

    /**
     * @var string $route
     */
    private $route;

    /**
     * @var array $collectedParameters
     */
    private $collectedParameters = [];

    /**
     * @var array $paramFetcher
     */
    private $paramFetcher;

    /**
     * @param RouterInterface        $router
     * @param ContainerInterface     $container
     * @param EntityManagerInterface $em
     * @param RequestStack           $requestStack
     * @param string                 $route
     */
    public function __construct(
        RouterInterface        $router,
        ContainerInterface     $container,
        EntityManagerInterface $em,
        RequestStack           $requestStack,
        string                 $route = 'cms_page'
    ) {
        $this->router       = $router;
        $this->container    = $container;
        $this->em           = $em;
        $this->pageRepo     = $em->getRepository(Page::class);
        $this->requestStack = $requestStack;
        $this->route        = $route;
        $this->paramFetcher = [$this->container, 'getParameter'];
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param int    $referenceType
     *
     * @return string|null
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        if ($name === $this->route) {
            $page          = null;
            $request       = $this->requestStack->getCurrentRequest();
            $routeParams   = $request->get('_route_params');
            /** @var Page $currentPage */
            $currentPage   = $routeParams['_page'];
            $defaultLocale = 'lv';
            $locale        = $parameters['_locale'] ?? $request->get('_locale', $defaultLocale);

            if (isset($parameters['_page_id'])) {
                $page = $this->pageRepo->find($parameters['_page_id']);

                unset($parameters['_page_id']);
            } else if (isset($parameters['_locale'])) {
                unset($parameters['_locale']);

                if ($locale == $currentPage->getLocale()) {
                    $page = $currentPage;
                } else if ($original = $currentPage->getOriginal()) {
                    $page = $locale == $defaultLocale ? $currentPage->getOriginal() : $original->getTranslation($locale);
                } else {
                    $page = $currentPage->getTranslation($locale);
                }
            }

            return $page ? $this->generatePagePath($request, $page, $parameters, $referenceType) : null;
        }

        return $this->router->generate($name, $parameters, $referenceType);
    }

    /**
     * @param Request $request
     * @param Page    $page
     * @param array   $parameters
     * @param int     $referenceType
     *
     * @return string
     */
    public function generatePagePath(Request $request, Page $page, array $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        $path = $page->getPath();

        if ($referenceType === self::ABSOLUTE_URL) {
            $path = $request->getSchemeAndHttpHost() . $path;
        }

        if ($parameters) {
            $path .= '?' . http_build_query($parameters);
        }

        return $path;
    }

    /**
     * @param string $pathinfo
     *
     * @return array
     */
    public function match($pathinfo)
    {
        /** @var Page|null $page */
        $page = $this->pageRepo->findOneBy([
            'path' => $pathinfo === '/' ? $pathinfo : rtrim($pathinfo, '/'),
        ]);

        if ($page) {
            return [
                '_locale'     => $page->getLocale(),
                '_route'      => $this->route,
                '_controller' => implode('::', [CmsController::class, 'page']),
                '_page'       => $page,
            ];
        }

        return $this->router->match($pathinfo);
    }

    /**
     * @param RequestContext $context
     */
    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }

    /**
     * @return RequestContext
     */
    public function getContext()
    {
        return $this->router->getContext();
    }

    /**
     * @return void
     */
    public function getRouteCollection()
    {
        $collection = $this->container->get('admin.route_loader')->load('admin', null);
        $this->resolveParameters($collection);
        $collection->addResource(new ContainerParametersResource($this->collectedParameters));

        try {
            $containerFile = ($this->paramFetcher)('kernel.cache_dir').'/'.($this->paramFetcher)('kernel.container_class').'.php';
            if (file_exists($containerFile)) {
                $collection->addResource(new FileResource($containerFile));
            } else {
                $collection->addResource(new FileExistenceResource($containerFile));
            }
        } catch (ParameterNotFoundException $exception) {
        }

        $collection->addCollection($this->router->getRouteCollection());

        return $collection;
    }

    /**
     * @param RouteCollection $collection
     */
    private function resolveParameters(RouteCollection $collection)
    {
        foreach ($collection as $route) {
            foreach ($route->getDefaults() as $name => $value) {
                $route->setDefault($name, $this->resolve($value));
            }

            foreach ($route->getRequirements() as $name => $value) {
                $route->setRequirement($name, $this->resolve($value));
            }

            $route->setPath($this->resolve($route->getPath()));
            $route->setHost($this->resolve($route->getHost()));

            $schemes = [];
            foreach ($route->getSchemes() as $scheme) {
                $schemes = array_merge($schemes, explode('|', $this->resolve($scheme)));
            }
            $route->setSchemes($schemes);

            $methods = [];
            foreach ($route->getMethods() as $method) {
                $methods = array_merge($methods, explode('|', $this->resolve($method)));
            }
            $route->setMethods($methods);
            $route->setCondition($this->resolve($route->getCondition()));
        }
    }

    /**
     * @param $value
     *
     * @return array|string|string[]|null
     */
    private function resolve($value)
    {
        if (\is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->resolve($val);
            }

            return $value;
        }

        if (!\is_string($value)) {
            return $value;
        }

        $escapedValue = preg_replace_callback('/%%|%([^%\s]++)%/', function ($match) use ($value) {
            // skip %%
            if (!isset($match[1])) {
                return '%%';
            }

            if (preg_match('/^env\((?:\w++:)*+\w++\)$/', $match[1])) {
                throw new RuntimeException(sprintf('Using "%%%s%%" is not allowed in routing configuration.', $match[1]));
            }

            $resolved = ($this->paramFetcher)($match[1]);

            if (\is_bool($resolved)) {
                $resolved = (string) (int) $resolved;
            }

            if (\is_string($resolved) || is_numeric($resolved)) {
                $this->collectedParameters[$match[1]] = $resolved;

                return (string) $this->resolve($resolved);
            }

            throw new RuntimeException(sprintf('The container parameter "%s", used in the route configuration value "%s", must be a string or numeric, but it is of type "%s".', $match[1], $value, \gettype($resolved)));
        }, $value);

        return str_replace('%%', '%', $escapedValue);
    }
}