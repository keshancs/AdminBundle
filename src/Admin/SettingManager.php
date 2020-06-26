<?php

namespace AdminBundle\Admin;

use AdminBundle\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;

final class SettingManager
{
    /**
     * @var Setting[]
     */
    private $settings;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->settings = $em
            ->getRepository(Setting::class)
            ->createQueryBuilder('o', 'o.name')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function get(string $name)
    {
        return json_decode($this->settings[$name]['value'], true);
    }
}