<?php

namespace AdminBundle\Admin;

class TemplateRegistry implements TemplateRegistryInterface
{
    /**
     * @var array
     */
    private $templates = [
        'list'   => '@Admin/CRUD/list.html.twig',
        'create' => '@Admin/CRUD/create.html.twig',
        'edit'   => '@Admin/CRUD/edit.html.twig',
    ];

    /**
     * @inheritDoc
     */
    public function getTemplate(string $name)
    {
        return $this->templates[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setTemplate(string $name, string $path)
    {
        $this->templates[$name] = $path;
    }
}