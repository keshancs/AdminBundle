<?php

namespace AdminBundle\Admin;

class ListMapper
{
    /**
     * @var string $identifier
     */
    private $identifier;

    public function __construct()
    {
        $this->identifier = 'id';
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     *
     * @return ListMapper
     */
    public function setIdentifier(string $identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }
}