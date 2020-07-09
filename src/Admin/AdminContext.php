<?php

namespace AdminBundle\Admin;

use AdminBundle\Collection\ArrayCollection;
use AdminBundle\Filter\Filter;
use AdminBundle\Mapper\FilterMapper;
use AdminBundle\Mapper\FormMapper;
use AdminBundle\Mapper\ListColumnDescriptor;
use AdminBundle\Mapper\ListMapper;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class AdminContext
{
    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var ListColumnDescriptor[]
     */
    private $list;

    /**
     * @var FormInterface|null
     */
    protected $form;

    /**
     * @var string
     */
    protected $formTheme;

    /**
     * @var FormBuilderInterface
     */
    private $filterFormBuilder;

    /**
     * @var Filter[]
     */
    private $filters;

    /**
     * @var array
     */
    private $results;

    /**
     * @var int
     */
    private $resultCount;

    /**
     * @var ArrayCollection
     */
    private $formTabs;

    /**
     * @var bool
     */
    private $showPageSidebar = false;

    /**
     * @param AdminInterface $admin
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function __construct(AdminInterface $admin)
    {
        $this->admin = $admin;

        if ($admin->isAction('list')) {
            $alias = ($qb = $admin->createQuery())->getRootAliases()[0];

            $admin->configureListFields($listMapper = new ListMapper($admin, $qb, $alias));

            $this->list        = $listMapper->getList();
            $this->results     = $this->applyTransforms($qb->getQuery()->getResult());
            $this->resultCount = $qb->select($qb->expr()->count($alias))->getQuery()->getSingleScalarResult();

            if ($this->hasResults()) {
                $filters      = $admin->getRequest()->get('filter', []);
                $filterMapper = new FilterMapper($admin, $qb, $alias, $filters);

                $admin->configureFilters($filterMapper);

                $this->filters           = $filters;
                $this->filterFormBuilder = $filterMapper->getFormBuilder();
            }
        }

        $this->formTabs  = new ArrayCollection();
        $this->formTheme = '@Admin/form_fields.html.twig';
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
     * @param FormBuilderInterface $builder
     */
    public function configureFormBuilder(FormBuilderInterface $builder)
    {
        $this->admin->configureFormFields(new FormMapper($this->admin, $builder));
    }

    /**
     * @return FormBuilderInterface
     */
    public function getFormBuilder()
    {
        $formBuilder = $this->admin->getFormFactory()->createNamedBuilder(
            $this->admin->getName(),
            FormType::class,
            null,
            [
                'data_class' => $this->admin->getClass(),
            ]
        );

        $this->configureFormBuilder($formBuilder);

        return $formBuilder;
    }

    /**
     * @return string
     */
    public function getFormTheme()
    {
        return $this->formTheme;
    }

    /**
     * @param string $formTheme
     */
    public function setFormTheme(string $formTheme)
    {
        $this->formTheme = $formTheme;
    }

    /**
     * @param array $results
     *
     * @return array
     */
    private function applyTransforms($results)
    {
        foreach ($results as $i => $row) {
            foreach ($row as $propertyPath => $value) {
                if (isset($this->list[$propertyPath])) {
                    $listColumnDescriptor = $this->list[$propertyPath];

                    if ($transformer = $listColumnDescriptor->getDataTransformer()) {
                        $value = $transformer->transform($value);
                    }
                }

                $results[$i][$propertyPath] = $value;
            }
        }

        return $results;
    }

    /**
     * @return FormView
     */
    public function getFilters()
    {
        return $this->filterFormBuilder->getForm()->createView();
    }

    /**
     * @return ListColumnDescriptor[]
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @return bool
     */
    public function hasFilters()
    {
        return !empty($this->filters);
    }

    /**
     * @return bool
     */
    public function hasResults()
    {
        return $this->resultCount > 0;
    }

    /**
     * @return ArrayCollection
     */
    public function getFormTabs()
    {
        return $this->formTabs;
    }

    /**
     * @return bool
     */
    public function isShowPageSidebar()
    {
        return $this->showPageSidebar;
    }

    /**
     * @param bool $showPageSidebar
     *
     * @return AdminContext
     */
    public function setShowPageSidebar($showPageSidebar)
    {
        $this->showPageSidebar = $showPageSidebar;

        return $this;
    }
}