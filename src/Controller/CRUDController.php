<?php

namespace App\AdminBundle\Controller;

use App\AdminBundle\Admin\AdminInterface;
use Doctrine\ORM\QueryBuilder;
use ReflectionClass;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CRUDController
 *
 * @copyright 2020, htdocs
 * @package   App\AdminBundle\Controller
 * @author    George Klavinsh
 *
 * @property AdminInterface $admin
 */
class CRUDController extends CoreController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function list(Request $request)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb
            ->select('o')
            ->from($this->admin->getClass(), 'o')
        ;

        // TODO: select per page
        // TODO: iterate

        $template = $this->admin->getTemplate('list');

        return $this->render($template, [
            'admin'  => $this->admin,
            'result' => $qb->getQuery()->getArrayResult(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Request $request)
    {
        $id     = $request->get($this->admin->getIdentifier());
        $object = $this->admin->getObject($id);

        /** @var FormInterface $form */
        $form = $this->admin->getForm();
        $form->setData($object)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('admin_flash_success', $this->admin->trans('admin.flash.edit_success'));

            return $this->redirectToRoute($request->get('_route'), ['id' => $object->getId()]);
        }

        return $this->render('@Admin/CRUD/edit.html.twig', [
            'admin' => $this->admin,
            'form'  => $form->createView(),
        ]);
    }
}