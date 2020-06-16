<?php

namespace AdminBundle\Admin;

use AdminBundle\Controller\AdminController;
use AdminBundle\Route\RouteGenerator;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractAdmin implements AdminInterface, TranslatorInterface
{
    /**
     * @var string $controller
     */
    protected $controller;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var ListMapper|null
     */
    protected $list;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var FormInterface|null
     */
    protected $form;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var RouteGenerator
     */
    protected $routeGenerator;

    /**
     * @var string[] $templates
     */
    protected $templates = [
        'list' => '@Admin/CRUD/list.html.twig',
        'edit' => '@Admin/CRUD/edit.html.twig',
    ];

    /**
     * @var string
     */
    protected $identifier = 'id';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string[]
     */
    protected $routes = [];

    /**
     * @var object|null
     */
    protected $subject;

    /**
     * @var array
     */
    protected $formOptions = [];

    /**
     * @param string $code
     * @param string $class
     *
     * @throws ReflectionException
     */
    public function __construct(string $code, string $class)
    {
        $this->controller = AdminController::class;
        $this->code       = $code;
        $this->class      = $class;
        $this->name       = strtolower((new ReflectionClass($class))->getShortName());
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @inheritDoc
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ListMapper|null
     */
    public function getList()
    {
        $this->buildList();

        return $this->list;
    }

    /**
     *
     */
    private function buildList()
    {
        $this->list = new ListMapper();
    }

    /**
     * @return FormInterface|null
     */
    public function getForm()
    {
        return $this->buildForm();
    }

    /**
     * @return FormInterface|null
     */
    public function buildForm()
    {
        if (!$this->form) {
            $this->form = $this->getFormBuilder()->getForm();
        }

        return $this->form;
    }

    /**
     * @return object|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param object|null $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return FormBuilderInterface
     */
    public function getFormBuilder()
    {
        $this->formOptions['data_class'] = $this->getClass();

        $formBuilder = $this->getFormFactory()->createNamedBuilder(
            $this->name,
            FormType::class,
            null,
            $this->formOptions
        );

        $this->configureFormBuilder($formBuilder);

        return $formBuilder;
    }

    /**
     * @param FormBuilderInterface $builder
     */
    private function configureFormBuilder(FormBuilderInterface $builder)
    {
        $mapper = new FormMapper($builder);

        $this->configureFormFields($mapper);
    }

    /**
     * @param Pool $pool
     */
    public function setPool($pool)
    {
        $this->pool = $pool;
    }

    /**
     * @param RouterInterface $router
     */
    public function setRouter($router)
    {
        $this->router = $router;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @param string $route
     *
     * @return string|null
     */
    public function getRoute(string $route)
    {
        return $this->routes[$route] ?? null;
    }

    /**
     * @param array $routes
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     *
     * @return AbstractAdmin
     */
    public function setController(string $controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @param string $route
     *
     * @return string|null
     */
    public function getTemplate(string $route)
    {
        return $this->templates[$route] ?? null;
    }

    /**
     * @param string $route
     * @param string $template
     */
    public function setTemplate(string $route, string $template)
    {
        $this->templates[$route] = $template;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     *
     * @return object|null
     */
    public function getObject(string $identifier)
    {
        return $this->getEntityManager()->find($this->getClass(), $identifier);
    }

    /**
     * @return FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @param EntityManagerInterface $em
     */
    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param RouteGenerator $routeGenerator
     */
    public function setRouteGenerator(RouteGenerator $routeGenerator)
    {
        $this->routeGenerator = $routeGenerator;
    }

    /**
     * @inheritDoc
     */
    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null)
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param int    $referenceType
     *
     * @return string|null
     */
    public function generateUrl($name, array $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if (!isset($this->routes[$name])) {
            return null;
        }

        return $this->routeGenerator->generate($this->routes[$name], $parameters, $referenceType);
    }

    /**
     * @inheritDoc
     */
    public function configureListFields(ListMapper $listMapper)
    {
    }

    /**
     * @inheritDoc
     */
    public function configureFormFields(FormMapper $formMapper)
    {
    }
}