<?php

namespace AdminBundle\Admin;

use AdminBundle\Collection\ArrayCollection;
use AdminBundle\Filter\Filter;
use AdminBundle\Mapper\FilterMapper;
use AdminBundle\Mapper\ListColumnDescriptor;
use AdminBundle\Mapper\ListMapper;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;

class Page
{
    /**
     * @var ListColumnDescriptor[]
     */
    private $list;

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
     * @param AdminInterface $admin
     * @param array          $filters
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function __construct(AdminInterface $admin, array $filters)
    {
        if ($admin->isAction('list')) {
            $alias = ($qb = $admin->createQuery())->getRootAliases()[0];

            $admin->configureListFields($listMapper = new ListMapper($admin, $qb, $alias));

            $this->list        = $listMapper->getList();
            $this->results     = $this->applyTransforms($qb->getQuery()->getResult());
            $this->resultCount = $qb->select($qb->expr()->count($alias))->getQuery()->getSingleScalarResult();

            if ($this->hasResults()) {
                $admin->configureFilters($filterMapper = new FilterMapper($admin, $qb, $alias, $filters));

                $this->filters           = $filters;
                $this->filterFormBuilder = $filterMapper->getFormBuilder();
            }
        }

        $this->formTabs = new ArrayCollection();
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
}