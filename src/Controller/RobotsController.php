<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RobotsController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $sitemap  = $this->generateUrl('admin_sitemap');

        $response = new Response(sprintf("User-agent: *\r\nSitemap: %s", $sitemap));
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }
}