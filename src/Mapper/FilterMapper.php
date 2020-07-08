<?php

namespace AdminBundle\Mapper;

use AdminBundle\Admin\AdminInterface;
use AdminBundle\Filter\Filter;
use AdminBundle\Filter\FilterInterface;
use AdminBundle\Filter\Type\NumberType;
use AdminBundle\Filter\Type\TextType;
use AdminBundle\Utils\TranslationUtils;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;

class FilterMapper
{
    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var QueryBuilder
     */
    private $qb;

    /**
     * @var FormBuilderInterface
     */
    private $formBuilder;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var FilterInterface[]
     */
    private $filters;

    /**
     * @var string
     */
    private $defaultFilterType = TextType::class;

    /**
     * @param AdminInterface $admin
     * @param QueryBuilder   $qb
     * @param string         $alias
     * @param array          $filters
     */
    public function __construct(AdminInterface $admin, QueryBuilder $qb, string $alias, array $filters)
    {
        $this->admin = $admin;
        $this->qb    = $qb;
        $this->alias = $alias;

        $this->processFilters($filters);
    }

    /**
     * @param array $filters
     */
    private function processFilters(array $filters)
    {
        foreach ($filters as $propertyPath => $data) {
            $filters[$propertyPath] = new Filter($propertyPath, $data['type'] ?? 'eq', $data['value'] ?? $data);
        }

        $this->filters = $filters;
    }

    /**
     * @param string $propertyPath
     * @param string $type
     * @param array  $options
     *
     * @return FilterMapper
     */
    public function add(string $propertyPath, $type = null, array $options = [])
    {
        $filterOptions = [
            'attr'        => ['class' => 'd-flex flex-fill'],
            'label'       => sprintf('admin.filter.label_%s', TranslationUtils::camelCaseToSnakeCase($propertyPath)),
            'label_attr'  => ['class' => 'font-weight-bold w-25 flex-shrink-0'],
            'row_attr'    => ['class' => 'form-group d-flex align-items-center'],
        ];

        // TODO: move this into a separate method something like "try to guess the filter type"
        if (null === $type) {
            $type = $this->defaultFilterType;

            try {
                $fieldMapping = $this->admin->getClassMetadata()->getFieldMapping($propertyPath);

                switch ($fieldMapping['type']) {
                    case 'integer':
                    case 'float':
                    case 'double':
                        $type = NumberType::class;
                        break;
                }
            } catch (MappingException $e) {
            }
        }

        if ($filter = $this->filters[$propertyPath] ?? null) {
            if (in_array($type, [NumberType::class, TextType::class])) {
                $filterOptions['type']  = $filter->getType();
                $filterOptions['value'] = $filter->getValue();
            } else {
                $filterOptions['data'] = $filter->getValue();
            }

            if (isset($options['query_builder']) && is_callable($options['query_builder'])) {
                call_user_func($options['query_builder'], $this->qb);
            } else if ($value = $filter->getValue()) {
                $parameter = sprintf(':%s', $filter->getPropertyPath());

                if ($filter->getType() === 'like') {
                    $value = sprintf('%%%s%%', $value);
                }

                $this->qb
                    ->andWhere(
                        call_user_func_array(
                            [
                                $this->qb->expr(),
                                $filter->getType()
                            ],
                            [
                                sprintf('%s.%s', $this->alias, $filter->getPropertyPath()),
                                $parameter
                            ]
                        )
                    )
                    ->setParameter($filter->getPropertyPath(), $value)
                ;
            }
        }

        $this->getFormBuilder()->add($propertyPath, $type, array_merge($filterOptions, $options));

        return $this;
    }

    /**
     * @return FormBuilderInterface
     */
    public function getFormBuilder()
    {
        if (!$this->formBuilder) {
            $this->formBuilder = $this->admin->getFormFactory()->createNamedBuilder('filter', FormType::class, null, [
                'method'          => 'GET',
                'attr'            => ['novalidate' => 'novalidate'],
                'csrf_protection' => false,
            ]);
        }

        return $this->formBuilder;
    }
}
