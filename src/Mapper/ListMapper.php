<?php

namespace AdminBundle\Mapper;

use AdminBundle\Admin\AdminInterface;
use Doctrine\ORM\QueryBuilder;

class ListMapper
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
     * @var string
     */
    private $alias;

    /**
     * @var array
     */
    private $list = [];

    /**
     * @param AdminInterface $admin
     * @param QueryBuilder   $qb
     * @param string         $alias
     */
    public function __construct(AdminInterface $admin, QueryBuilder $qb, string $alias)
    {
        $this->admin = $admin;
        $this->qb    = $qb/*->resetDQLPart('select')*/;
        $this->alias = $alias;
    }

    /**
     * @param string $propertyPath
     * @param array  $options
     *
     * @return ListMapper
     */
    public function add(string $propertyPath, array $options = [])
    {
        $this->list[$propertyPath] = new ListColumnDescriptor($propertyPath, $options);

        /*$this->qb->addSelect(sprintf('%s.%s', $this->alias, $propertyPath));*/

        return $this;
    }

    /**
     * @param string $propertyPath
     *
     * @return ListColumnDescriptor|null
     */
    public function get(string $propertyPath)
    {
        return $this->list[$propertyPath] ?? null;
    }

    /**
     * @return array
     */
    public function getList()
    {
        if (empty($this->list) && $identifier = $this->admin->getIdentifier()) {
            $this->add($identifier);
        }

        return $this->list;
    }
}