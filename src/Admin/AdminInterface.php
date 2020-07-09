<?php

namespace AdminBundle\Admin;

use AdminBundle\Mapper\FilterMapper;
use AdminBundle\Mapper\FormMapper;
use AdminBundle\Mapper\ListMapper;
use AdminBundle\Routing\Router;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

interface AdminInterface extends TemplateRegistryInterface, TranslatorInterface
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
     * @return string
     */
    public function getController();

    /**
     * @return object|null
     */
    public function getSubject();

    /**
     * @return AdminContext
     */
    public function getContext();

    /**
     * @return Request
     */
    public function getRequest();

    /**
     * @param Request $request
     */
    public function setRequest(Request $request);

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
     * @return string
     */
//    public function getFormTheme();

    /**
     * @param string $formTheme
     */
//    public function setFormTheme(string $formTheme);

    /**
     * @param Request $request
     */
    public function configure(Request $request);

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
     * @param Router $router
     */
    public function configureRoutes(Router $router);

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
    public function generateObjectUrl(
        $name,
        $objectId,
        array $parameters = [],
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    );

    /**
     * @param string $action
     *
     * @return bool
     */
    public function isAction(string $action);

    /**
     * @param string $name
     *
     * @return string
     */
    public function getRouteName(string $name);

    /**
     * @param string $path
     *
     * @return string
     */
    public function getRoutePath(string $path);
}
