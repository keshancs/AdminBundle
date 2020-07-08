<?php

namespace AdminBundle\Controller;

use AdminBundle\Entity\Page;
use AdminBundle\Routing\RouteLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class SitemapController extends AbstractController
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
     * @param RouterInterface $router
     * @param RouteLoader     $routeLoader
     */
    public function __construct(RouterInterface $router, RouteLoader $routeLoader)
    {
        $this->router      = $router;
        $this->routeLoader = $routeLoader;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $urls        = [];
        $hostname    = $request->getSchemeAndHttpHost();
        $routes      = clone $this->router->getRouteCollection();
        $routePrefix = $this->routeLoader->getRoutePrefix();

        $routes->remove($routePrefix . '_sitemap');
        $routes->remove($routePrefix . '_robots');

        foreach ($this->get('admin.pool')->getServices() as $code => $admin) {
            foreach ($admin->getRoutes() as $name => $route) {
                $routes->remove($route);
            }

            foreach ($this->routeLoader->getRoutes() as $route => $config) {
                if ($config['crud']) {
                    continue;
                }

                $routes->remove($routePrefix . '_' . $route);
            }
        }

        /** @var EntityManagerInterface $em */
        $em             = $this->getDoctrine()->getManager();
        $qb             = $em->getRepository(Page::class)->createQueryBuilder('p')->select('p.id');
        $pageIds        = array_column($qb->getQuery()->getResult(), 'id');
        $adminExtension = $this->get('admin.twig.admin_extension');

        foreach ($pageIds as $pageId) {
            $url = $adminExtension->generateCmsPageUrl(['id' => $pageId]);

            $urls[] = ['loc' => $url];
        }

        foreach ($routes as $name => $route) {
            if (false === strpos($name, '_')) {
                $url = $this->router->generate($name);

                $urls[] = ['loc' => $url];
            }
        }

        $body = $this->renderView('@Admin/Sitemap/sitemap.html.twig', ['urls' => $urls, 'hostname' => $hostname]);

        $response = new Response($body);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}