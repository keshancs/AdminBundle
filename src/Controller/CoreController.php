<?php

namespace AdminBundle\Controller;

use AdminBundle\Admin\AdminInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CoreController extends AbstractController
{
    /**
     * @var AdminInterface
     */
    protected $admin;

    /**
     * @param AdminInterface $admin
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
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
     * @param string              $name
     * @param AdminInterface|null $admin
     * @param string|int|null     $id
     * @param array               $parameters
     *
     * @return string
     */
    protected function generateAdminUrl(string $name, AdminInterface $admin = null, $id = null, array $parameters = [])
    {
        if (null === $admin) {
            $admin = $this->admin;
        }

        if ($id) {
            return $admin->generateObjectUrl($name, $id, $parameters);
        }

        return $admin->generateUrl($name, $parameters);
    }
}