<?php

namespace AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends CRUDController
{
    public function index()
    {
        return $this->redirectToAdminRoute('dashboard');
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
}