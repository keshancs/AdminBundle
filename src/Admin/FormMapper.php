<?php

namespace App\AdminBundle\Admin;

use Symfony\Component\Form\FormBuilderInterface;

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
            'label' => $this->getLabel($name),
            'label_attr' => [
                'class' => 'col-lg-3 col-form-label',
            ],
            'row_attr' => [
                'class' => 'form-group row',
            ],
            'attr' => [
                'class' => 'form-control m-b',
            ],
        ];

        $this->formBuilder->add($name, $type, array_merge($defaultOptions, $options));

        return $this;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function getLabel(string $name)
    {
        // TODO: Utils
        preg_match_all('/([A-Z])?([a-z]+)/', $name, $matches);

        $label = implode('_', array_map('strtolower', $matches[0]));

        return sprintf('admin.form.label_%s', $label);
    }
}