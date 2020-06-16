<?php

namespace AdminBundle\Admin;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface AdminInterface
{
    /**
     * @return string
     */
    public function getClass();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper);

    /**
     * @param FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper);

    /**
     * @param string $name
     * @param array  $parameters
     * @param int    $referenceType
     *
     * @return string|null
     */
    public function generateUrl($name, array $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH);
}