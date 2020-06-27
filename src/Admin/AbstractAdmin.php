<?php

namespace AdminBundle\Admin;

use AdminBundle\Controller\CmsController;
use AdminBundle\Mapper\FilterMapper;
use AdminBundle\Mapper\FormMapper;
use AdminBundle\Mapper\ListMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ObjectRepository;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractAdmin implements AdminInterface, TranslatorInterface, TemplateRegistryInterface
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
     * @var Request
     */
    protected $request;

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
     * @var TemplateRegistryInterface
     */
    protected $templateRegistry;

    /**
     * @var SettingManager
     */
    protected $settingManager;

    /**
     * @var Page
     */
    protected $page;

    /**
     * @var ClassMetadata
     */
    protected $classMetadata;

    /**
     * @var string|null
     */
    protected $identifier;

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
     * @var string[]
     */
    private $sorting = [
        'sort_by'    => 'id',
        'sort_order' => 'ASC',
    ];

    /**
     * @param string $code
     * @param string $class
     *
     * @throws ReflectionException
     */
    public function __construct(string $code, string $class)
    {
        $this->controller       = CmsController::class;
        $this->code             = $code;
        $this->class            = $class;
        $this->name             = strtolower((new ReflectionClass($class))->getShortName());
        $this->templateRegistry = new TemplateRegistry();
    }

    /**
     * @return TemplateRegistryInterface
     */
    public function getTemplateRegistry()
    {
        return $this->templateRegistry;
    }

    /**
     * @inheritDoc
     */
    public function getTemplate(string $name)
    {
        return $this->getTemplateRegistry()->getTemplate($name);
    }

    /**
     * @inheritDoc
     */
    public function setTemplate(string $name, string $path)
    {
        $this->getTemplateRegistry()->setTemplate($name, $path);
    }

    /**
     * @return SettingManager
     */
    public function getSettingManager()
    {
        return $this->settingManager;
    }

    /**
     * @param SettingManager $settingManager
     */
    public function setSettingManager($settingManager)
    {
        $this->settingManager = $settingManager;
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
     * @inheritDoc
     */
    public function getPage()
    {
        $this->buildPage();

        return $this->page;
    }

    /**
     * @return Page
     */
    private function buildPage()
    {
        if (!$this->page) {
            $filters  = $this->request->get('filter', []);
            $page     = new Page($this, $filters);

            $this->page = $page;
        }

        return $this->page;
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
        $mapper = new FormMapper($this, $builder);

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
    public function getRouteName(string $route)
    {
        return 'admin_' . $this->name . '_' . $route;
    }

    /**
     * @param string $route
     *
     * @return string|null
     */
    public function getRoutePath(string $route)
    {
        return $this->routes[$route]['path'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setRoute(string $route, string $routeName, string $routePath)
    {
        $this->routes[$route] = ['name' => $routeName, 'path' => $routePath];
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
     * @return string
     * @throws MappingException
     */
    public function getIdentifier()
    {
        if (!$this->identifier) {
            $this->identifier = $this->classMetadata->getSingleIdentifierFieldName();
        }

        return $this->identifier;
    }

    /**
     * @inheritDoc
     */
    public function getObject($id)
    {
        $object = $this->getRepository()->find($id);

        $this->setSubject($object);

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function newInstance()
    {
        $class    = $this->getClass();
        $instance = new $class();

        $this->setSubject($instance);

        return $instance;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request|null
     */
    public function getRequest()
    {
        return $this->request;
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
     * @param EntityManagerInterface $em
     */
    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->em            = $em;
        $this->classMetadata = $em->getClassMetadata($this->getClass());
    }

    /**
     * @inheritDoc
     */
    public function getClassMetadata()
    {
        return $this->classMetadata;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function isAction(string $action)
    {
        return $this->request->get('_route') == $this->getRouteName($action);
    }

    /**
     * @inheritDoc
     */
    public function createQuery()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('o')
            ->from($this->getClass(), 'o')
            ->orderBy(sprintf('o.%s', $this->sorting['sort_by']), $this->sorting['sort_order'])
        ;

        return $qb;
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository($this->getClass());
    }

    /**
     * @inheritDoc
     */
    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null)
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * @inheritDoc
     */
    public function generateUrl($name, array $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if (0 !== strpos($name, 'admin_')) {
            $name = $this->getRouteName($name);
        }

        return $this->router->generate($name, $parameters, $referenceType);
    }

    /**
     * @inheritDoc
     */
    public function generateObjectUrl($name, $objectId, array $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $parameters['id'] = $objectId;

        return $this->generateUrl($name, $parameters, $referenceType);
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

    /**
     * @inheritDoc
     */
    public function configureFilters(FilterMapper $filterMapper)
    {
    }
}