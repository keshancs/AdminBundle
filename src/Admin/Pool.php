<?php

namespace AdminBundle\Admin;

class Pool
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * @var array
     */
    private $services = [];

    /**
     * @var array
     */
    private $blocks = [];

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array|AdminInterface[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param array $services
     */
    public function setServices($services)
    {
        $this->services = $services;
    }

    /**
     * @param string $code
     *
     * @return AdminInterface|null
     */
    public function getAdminByAdminCode(string $code)
    {
        return $this->services[$code] ?? null;
    }

    /**
     * @return array
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * @param array $blocks
     */
    public function setBlocks($blocks)
    {
        $this->blocks = $blocks;
    }
}
