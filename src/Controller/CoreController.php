<?php

namespace AdminBundle\Controller;

use AdminBundle\Admin\AdminInterface;
use Exception;
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
     * @param Request $request
     *
     * @return RedirectResponse
     */
    protected function refresh(Request $request)
    {
        return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
    }

    /**
     * @param string              $route
     * @param AdminInterface|null $admin
     * @param string|int|null     $id
     * @param array               $parameters
     * @param int                 $status
     *
     * @return RedirectResponse
     */
    protected function redirectToAdminRoute(string $route, AdminInterface $admin = null, $id = null, array $parameters = [], int $status = 302)
    {
        if (null === $admin) {
            $admin = $this->admin;
        }

        if ($id) {
            $url = $admin->generateObjectUrl($route, $id, $parameters);
        } else {
            $url = $admin->generateUrl($route, $parameters);
        }

        return $this->redirect($url, $status);
    }
}