<?php

namespace AdminBundle\Mapper;

use AdminBundle\Admin\AdminInterface;
use AdminBundle\Utils\TranslationUtils;
use Symfony\Component\Form\FormBuilderInterface;

class FormMapper
{
    /**
     * @var AdminInterface $admin
     */
    private $admin;

    /**
     * @var FormBuilderInterface $formBuilder
     */
    private $formBuilder;

    /**
     * @var string|null $tab
     */
    private $tab;

    /**
     * @param AdminInterface       $admin
     * @param FormBuilderInterface $formBuilder
     */
    public function __construct(AdminInterface $admin, FormBuilderInterface $formBuilder)
    {
        $this->admin       = $admin;
        $this->formBuilder = $formBuilder;
    }

    /**
     * @param string $name
     *
     * @return FormMapper
     */
    public function beginTab(string $name)
    {
        $this->admin->getContext()->getFormTabs()->set($this->tab = $name, []);

        return $this;
    }

    /**
     * @return FormMapper
     */
    public function endTab()
    {
        $this->tab = null;

        return $this;
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
        if ($this->tab) {
            $this->admin->getContext()->getFormTabs()->add($this->tab, $name);
        }

        $defaultOptions = [
            'label'      => sprintf(
                'admin.form.%s.label_%s',
                $this->admin->getName(),
                TranslationUtils::camelCaseToSnakeCase($name)
            ),
            'row_attr'   => ['class' => 'form-group'],
            'attr'       => ['class' => 'form-control'],
        ];

        $this->formBuilder->add($name, $type, array_merge($defaultOptions, $options));

        return $this;
    }
}
