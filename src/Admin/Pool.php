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
}
