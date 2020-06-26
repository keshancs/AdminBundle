<?php

namespace AdminBundle\Filter;

class Filter implements FilterInterface
{
    /**
     * @var string
     */
    private $propertyPath;

    /**
     * @var mixed
     */
    private $type;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @inheritDoc
     */
    public function __construct(string $propertyPath, $type, $value)
    {
        // TODO: FilterTypeResolver e.g. 1 => equals, 2 => not equals, etc. (for the QueryBuilder)
        $this->propertyPath = $propertyPath;
        $this->type         = $type;
        $this->value        = $value;
    }

    /**
     * @inheritDoc
     */
    public function getPropertyPath()
    {
        return $this->propertyPath;
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->value;
    }
}