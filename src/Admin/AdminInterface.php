<?php

namespace AdminBundle\Admin;

use AdminBundle\Mapper\FilterMapper;
use AdminBundle\Mapper\FormMapper;
use AdminBundle\Mapper\ListMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormFactoryInterface;
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
     * @return Page
     */
    public function getPage();

    /**
     * @param EntityManagerInterface $em
     */
    public function setEntityManager(EntityManagerInterface $em);

    /**
     * @return ClassMetadata
     */
    public function getClassMetadata();

    /**
     * @return FormFactoryInterface
     */
    public function getFormFactory();

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function setFormFactory(FormFactoryInterface $formFactory);

    /**
     * @return QueryBuilder
     */
    public function createQuery();

    /**
     * @param int|string $id
     *
     * @return object|null
     */
    public function getObject($id);

    /**
     * @return object
     */
    public function newInstance();

    /**
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper);

    /**
     * @param FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper);

    /**
     * @param FilterMapper $filterMapper
     */
    public function configureFilters(FilterMapper $filterMapper);

    /**
     * @param string $name
     * @param array  $parameters
     * @param int    $referenceType
     *
     * @return string|null
     */
    public function generateUrl($name, array $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH);

    /**
     * @param string     $name
     * @param string|int $objectId
     * @param array      $parameters
     * @param int        $referenceType
     *
     * @return string|null
     */
    public function generateObjectUrl($name, $objectId, array $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH);

    /**
     * @param string $action
     *
     * @return bool
     */
    public function isAction(string $action);

    /**
     * @param string $route
     *
     * @return string|null
     */
    public function getRouteName(string $route);

    /**
     * @param string $route
     *
     * @return string|null
     */
    public function getRoutePath(string $route);

    /**
     * @param string $route
     * @param string $routeName
     * @param string $routePath
     */
    public function setRoute(string $route, string $routeName, string $routePath);
}