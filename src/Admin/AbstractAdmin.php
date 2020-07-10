<?php

namespace AdminBundle\Admin;

use AdminBundle\Controller\CmsController;
use AdminBundle\Mapper\FilterMapper;
use AdminBundle\Mapper\FormMapper;
use AdminBundle\Mapper\ListMapper;
use AdminBundle\Mapper\RouteMapper;
use AdminBundle\Routing\RouteLoader;
use AdminBundle\Routing\Router;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ObjectRepository;
use Exception;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class AbstractAdmin implements AdminInterface
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
     * @var Request|null
     */
    protected $request;

    /**
     * @var Router
     */
    protected $router;

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
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var AdminContext
     */
    protected $context;

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
     * @var string[]
     */
    protected $sorting = [
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
        $this->controller = CmsController::class;
        $this->code       = $code;
        $this->class      = $class;
        $this->name       = strtolower((new ReflectionClass($class))->getShortName());
    }

    /**
     * @return TemplateRegistryInterface
     */
    public function getTemplateRegistry()
    {
        if (null === $this->templateRegistry) {
            $this->templateRegistry = new TemplateRegistry();
        }

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
     * @param string $formTheme
     */
    public function setFormTheme(string $formTheme)
    {
        $this->getContext()->setFormTheme($formTheme);
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
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getContext()
    {
        if (null === $this->context) {
            try {
                $this->context = new AdminContext($this);
            } catch (Exception $e) {
            }
        }

        return $this->context;
    }

    /**
     * @inheritDoc
     */
    public function getSubject()
    {
        return $this->subject;
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
     * @param string $name
     *
     * @return string|null
     */
    public function getRouteName(string $name)
    {
        return $this->router->getRouteName($this->name . '_' . $name);
    }

    /**
     * @param string $path
     *
     * @return string|null
     */
    public function getRoutePath(string $path)
    {
        return $this->router->getRoutePath('/' . $this->name . '/' . $path);
    }

    /**
     * @return array|string[]
     */
    public function getRoutes()
    {
        return $this->routes;
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
        return $this->subject = $this->getRepository()->find($id);
    }

    /**
     * @inheritDoc
     */
    public function newInstance()
    {
        $class    = $this->getClass();
        $instance = new $class();

        return $this->subject = $instance;
    }

    /**
     * @inheritDoc
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @inheritDoc
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
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
        if (null === $this->request) {
            return false;
        }

        return $this->request->get('_action') == $action;
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
        return $this->router->generate($this->getRouteName($name), $parameters, $referenceType);
    }

    /**
     * @inheritDoc
     */
    public function generateObjectUrl(
        $name,
        $objectId,
        array $parameters = [],
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ) {
        $parameters['id'] = $objectId;

        return $this->generateUrl($name, $parameters, $referenceType);
    }

    /**
     * @inheritDoc
     */
    public function configure(Environment $environment, Request $request)
    {
        $this->request = $request;

        try {
            $this->getContext()->configure($environment, $request);
        } catch (NoResultException $e) {
        } catch (NonUniqueResultException $e) {
        }
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

    /**
     * @inheritDoc
     */
    public function configureRoutes(Router $router)
    {
    }
}
