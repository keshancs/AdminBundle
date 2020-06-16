<?php

namespace App\AdminBundle\Controller;

use App\AdminBundle\Admin\AdminInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class CoreController extends AbstractController
{
    /**
     * @var AdminInterface
     */
    protected $admin;

    /**
     * @inheritDoc
     */
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $previous = $this->container;
        $this->container = $container;

        $this->configure();

        return $previous;
    }

    /**
     * Configure
     */
    private function configure()
    {
        if ($adminCode = $this->getRequest()->get('_admin')) {
            $this->admin = $this->container->get('admin.pool')->getAdminByAdminCode($adminCode);
        }
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    /**
     * @param string $route
     * @param array  $parameters
     * @param int    $status
     *
     * @return RedirectResponse
     */
    protected function redirectToAdminRoute(string $route, array $parameters = [], int $status = 302)
    {
        $routeGenerator = $this->get('admin.route_generator');

        return $this->redirect($this->generateUrl($routeGenerator->getRouteName($route), $parameters), $status);
    }
}