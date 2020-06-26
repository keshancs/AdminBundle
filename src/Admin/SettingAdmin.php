<?php

namespace AdminBundle\Admin;

use AdminBundle\Controller\SettingController;
use AdminBundle\Transformer\LocaleTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Intl\Locales;

class SettingAdmin extends AbstractAdmin
{
    /**
     * @return string
     */
    public function getController()
    {
        return SettingController::class;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        $builder = $this->formFactory->createNamedBuilder($this->name);
        $builder
            ->add('layout', TextType::class, [
                'attr'     => ['class' => 'form-control'],
                'row_attr' => ['class' => 'form-group'],
            ])
            ->add('default_locale', ChoiceType::class, [
                'attr'     => ['class' => 'select2bs4 form-control'],
                'choices'  => array_flip(Locales::getLocales()),
                'row_attr' => ['class' => 'form-group'],
            ])
            ->add('front_locales', ChoiceType::class, [
                'attr'     => ['class' => 'select2bs4 form-control'],
                'choices'  => array_flip(Locales::getLocales()),
                'multiple' => true,
                'row_attr' => ['class' => 'form-group'],
            ])
        ;

        $builder->get('front_locales')->addModelTransformer(new LocaleTransformer());

        return $builder->getForm();
    }
}