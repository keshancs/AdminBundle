<?php

namespace AdminBundle\Controller;

use AdminBundle\Entity\Page;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CmsController extends CRUDController
{
    /**
     * @return RedirectResponse
     */
    public function index()
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->redirectToRoute('admin_login');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function dashboard(Request $request)
    {
        return $this->render('@Admin/dashboard.html.twig');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function page(Request $request)
    {
        /** @var Page $page */
        $page = $request->get('_page');

        return $this->render('@Admin/CMS/page.html.twig', ['page' => $page]);
    }
}