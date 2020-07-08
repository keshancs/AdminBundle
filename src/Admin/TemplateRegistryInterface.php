<?php

namespace AdminBundle\Admin;

interface TemplateRegistryInterface
{
    /**
     * @param string $name
     *
     * @return string
     */
    public function getTemplate(string $name);

    /**
     * @param string $name
     * @param string $path
     *
     * @return $this
     */
    public function setTemplate(string $name, string $path);
}