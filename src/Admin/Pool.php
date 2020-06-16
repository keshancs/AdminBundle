<?php

namespace AdminBundle\Admin;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Pool
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var array
     */
    private $services = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     *
     * @return Pool
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param array $services
     *
     * @return Pool
     */
    public function setServices($services)
    {
        $this->services = $services;

        return $this;
    }

    /**
     * @param string $code
     *
     * @return mixed
     */
    public function getAdminByAdminCode(string $code)
    {
        return $this->container->get($code);
    }
}