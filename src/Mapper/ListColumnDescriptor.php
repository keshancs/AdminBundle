<?php

namespace AdminBundle\Mapper;

use AdminBundle\Utils\TranslationUtils;
use Symfony\Component\Form\DataTransformerInterface;

class ListColumnDescriptor
{
    /**
     * @var string
     */
    private $propertyPath;

    /**
     * @var array
     */
    private $options;

    /**
     * @var DataTransformerInterface
     */
    private $dataTransformer;

    /**
     * @param string $propertyPath
     * @param array  $options
     */
    public function __construct(string $propertyPath, array $options = [])
    {
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
     * @return mixed|string
     */
    public function getLabel()
    {
        return $this->options['label'] ?? 'admin.list.label_' . TranslationUtils::getLabel($this->propertyPath);
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