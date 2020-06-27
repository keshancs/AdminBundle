<?php

namespace AdminBundle\Routing;

use AdminBundle\Controller\CmsController;
use AdminBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
     * @return RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->router->getRouteCollection();
    }
}