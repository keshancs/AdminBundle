<?php

namespace AdminBundle\Mapper;

use AdminBundle\Admin\AdminInterface;
use AdminBundle\Utils\TranslationUtils;
use Symfony\Component\Form\DataTransformerInterface;

class ListColumnDescriptor
{
    /**
     * @var AdminInterface $admin
     */
    private $admin;

    /**
     * @var string $propertyPath
     */
    private $propertyPath;

    /**
     * @var array $options
     */
    private $options;

    /**
     * @var DataTransformerInterface $dataTransformer
     */
    private $dataTransformer;

    /**
     * @param AdminInterface $admin
     * @param string         $propertyPath
     * @param array          $options
     */
    public function __construct(AdminInterface $admin, string $propertyPath, array $options = [])
    {
        $this->admin        = $admin;
        $this->propertyPath = $propertyPath;
        $this->options      = $options;
    }

    /**
     * @return string
     */
    public function getPropertyPath()
    {
        return $this->propertyPath;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->options['label'] ?? sprintf(
            'admin.list.%s.label_%s',
            $this->admin->getName(),
            TranslationUtils::camelCaseToSnakeCase($this->propertyPath)
        );
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param DataTransformerInterface $dataTransformer
     */
    public function setDataTransformer(DataTransformerInterface $dataTransformer)
    {
        $this->dataTransformer = $dataTransformer;
    }

    /**
     * @return DataTransformerInterface
     */
    public function getDataTransformer()
    {
        return $this->dataTransformer;
    }
}
