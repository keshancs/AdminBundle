<?php

namespace AdminBundle\Filter;

interface FilterInterface
{
    /**
     * @param string $propertyPath
     * @param mixed  $type
     * @param mixed  $value
     */
    public function __construct(string $propertyPath, $type, $value);

    /**
     * @return string
     */
    public function getPropertyPath();

    /**
     * @return mixed
     */
    public function getType();

    /**
     * @return mixed
     */
    public function getValue();
}