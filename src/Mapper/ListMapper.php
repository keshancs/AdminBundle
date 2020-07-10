<?php

namespace AdminBundle\Mapper;

use AdminBundle\Admin\AdminInterface;
use AdminBundle\Utils\TranslationUtils;
use Doctrine\ORM\QueryBuilder;
use Twig\Environment;
use Twig\Error\Error;

class ListMapper
{
    /**
     * @var Environment $environment
     */
    private $environment;

    /**
     * @var AdminInterface $admin
     */
    private $admin;

    /**
     * @var QueryBuilder $qb
     */
    private $qb;

    /**
     * @var string $alias
     */
    private $alias;

    /**
     * @var array $list
     */
    private $list = [];

    /**
     * @param Environment    $environment
     * @param AdminInterface $admin
     * @param QueryBuilder   $qb
     * @param string         $alias
     */
    public function __construct(Environment $environment, AdminInterface $admin, QueryBuilder $qb, string $alias)
    {
        $this->environment = $environment;
        $this->admin       = $admin;
        $this->qb          = $qb;
        $this->alias       = $alias;
    }

    /**
     * @param string $propertyPath
     * @param array  $options
     *
     * @return ListMapper
     */
    public function add(string $propertyPath, array $options = [])
    {
        try {
            $template = $this->environment->load($options['template'] ?? '@Admin/CRUD/list_widgets.html.twig');
        } catch (Error $e) {
            $template = null;
        }

        if ($template) {
            $options['template']   = $template;
            $options['block_name'] = $options['block_name'] ?? 'default';
        }

        if (!isset($options['label'])) {
            $label = TranslationUtils::camelCaseToSnakeCase($propertyPath);

            $options['label'] = sprintf('admin.list.%s.label_%s', $this->admin->getName(), $label);
        }

        $this->list[$propertyPath] = $options;

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