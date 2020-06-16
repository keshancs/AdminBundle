<?php
/**
 * Class|Interface SecurityController
 *
 * @copyright 2020, htdocs
 * @package   AdminBundle\Controller
 * @author    George Klavinsh
 */

namespace AdminBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function login(Request $request)
    {
        return $this->render('@Admin/Security/login.html.twig');
    }
}