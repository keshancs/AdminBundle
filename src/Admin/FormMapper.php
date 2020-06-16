<?php

namespace AdminBundle\Admin;

use Symfony\Component\Form\FormBuilderInterface;
use TranslationUtils;

class FormMapper
{
    /**
     * @var FormBuilderInterface $formBuilder
     */
    private $formBuilder;

    /**
     * @param FormBuilderInterface $formBuilder
     */
    public function __construct(FormBuilderInterface $formBuilder)
    {
        $this->formBuilder = $formBuilder;
    }

    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     *
     * @return FormMapper
     */
    public function add(string $name, string $type = null, array $options = [])
    {
        $defaultOptions = [
            'label'      => TranslationUtils::getLabel($name),
            'label_attr' => ['class' => 'col-lg-3 col-form-label'],
            'row_attr'   => ['class' => 'form-group row'],
            'attr'       => ['class' => 'form-control m-b'],
        ];

        $this->formBuilder->add($name, $type, array_merge($defaultOptions, $options));

        return $this;
    }
}