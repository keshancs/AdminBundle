<?php

namespace AdminBundle\Controller;

use AdminBundle\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class SettingController extends CRUDController
{
    /**
     * @inheritDoc
     */
    public function list(Request $request)
    {
        $modelData = [];
        $form      = $this->admin->getContext()->getForm();

        foreach ($this->admin->getRepository()->findAll() as $setting) {
            $modelData[$setting->getName()] = $setting->getValue();
        }

        $form->setData($modelData)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $settings = $form->getData();

            /** @var EntityManagerInterface $em */
            $em = $this->getDoctrine()->getManager();

            foreach ($settings as $name => $value) {
                if (!$setting = $this->admin->getRepository()->findOneBy(['name' => $name])) {
                    /** @var Setting $setting */
                    $setting = $this->admin->newInstance();
                    $setting->setName($name);

                    $em->persist($setting);
                }

                $setting->setValue($value);
            }

            $em->flush();
        }

        return $this->render('@Admin/CRUD/edit.html.twig', [
            'admin' => $this->admin,
            'form'  => $form->createView(),
        ]);
    }
}