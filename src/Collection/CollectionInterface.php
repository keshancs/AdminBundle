<?php

namespace AdminBundle\Collection;

interface CollectionInterface
{
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function get(string $name);

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function add(string $name, $value = null);

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function set(string $name, $value = null);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name);

    /**
     * @return bool
     */
    public function isEmpty();
}