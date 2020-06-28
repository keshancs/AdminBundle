<?php

namespace AdminBundle\Controller;

use AdminBundle\Admin\AbstractAdmin;
use Doctrine\ORM\Mapping\MappingException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Locales;

/**
 * Class CRUDController
 *
 * @copyright 2020, htdocs
 * @package   AdminBundle\Controller
 * @author    George Klavinsh
 *
 * @property AbstractAdmin $admin
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
        return $this->render($this->admin->getTemplate('list'), [
            'admin'  => $this->admin,
            'result' => $this->admin->getPage()->getResults(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function create(Request $request)
    {
        $object = $this->admin->newInstance();

        /** @var FormInterface $form */
        $form = $this->admin->getForm();
        $form->setData($object)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('admin_flash_success', $this->admin->trans('admin.flash.edit_success'));

            return $this->redirect($this->generateAdminUrl('edit', null, $object->getId()));
        }

        return $this->render($this->admin->getTemplate('create'), [
            'admin' => $this->admin,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws MappingException
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

            return $this->redirect($this->generateAdminUrl('edit', null, $object->getId()));
        }

        return $this->render($this->admin->getTemplate('edit'), [
            'admin' => $this->admin,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     * @throws MappingException
     */
    public function translate(Request $request)
    {
        $id     = $request->get($this->admin->getIdentifier());
        $object = $this->admin->getObject($id);

        if (!$locale = $request->get('locale', null)) {
            $this->addFlash('admin_flash_error', $this->admin->trans('admin.flash.translate_error'));

            return $this->redirect($this->generateAdminUrl('edit', null, $object->getId()));
        }

        $em          = $this->getDoctrine()->getManager();
        $translation = $this->admin->getRepository()->findOneBy(['original' => $object->getId(), 'locale' => $locale]);

        if ($translation) {
            $this->addFlash('admin_flash_info', $this->admin->trans('admin.flash.translate_info'));
        } else {
            $translation = clone $object;
            $translation
                ->setOriginal($object)
                ->setLocale($locale)
            ;

            $em->persist($translation);
            $em->flush();

            $this->addFlash('admin_flash_success', $this->admin->trans('admin.flash.translate_success'));
        }

        return $this->redirect($this->generateAdminUrl('edit', null, $translation->getId()));
    }
}