<?php

namespace AdminBundle\Collection;

// TODO: implement ArrayAccess and Countable interfaces
final class ArrayCollection extends AbstractCollection
{
    /**
     * @var array $collection
     */
    private $collection = [];

    /**
     * @var int $offset
     */
    private $offset = 0;

    /**
     * @param string $name
     *
     * @return array|null
     */
    public function get(string $name)
    {
        return $this->collection[$name] ?? null;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return ArrayCollection
     */
    public function add(string $name, $value = null)
    {
        if (!$this->has($name)) {
            $this->set($name, []);
        }

        $this->collection[$name][] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @param null   $value
     *
     * @return ArrayCollection
     */
    public function set(string $name, $value = null)
    {
        $this->collection[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return ArrayCollection
     */
    private function unset(string $name)
    {
        if ($this->has($name)) {
            unset($this->collection[$name]);
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name)
    {
        return isset($this->collection[$name]);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->count() == 0;
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        return next($this->collection);
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return key($this->collection);
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return key($this->collection) !== null;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        return reset($this->collection);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->collection);
    }
}