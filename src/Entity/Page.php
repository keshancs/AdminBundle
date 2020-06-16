<?php

namespace App\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="page")
 */
class Page
{
    /**
     * @var int $id
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="page_id", type="integer")
     */
    private $id;

    /**
     * @var Page|null $parent
     *
     * @ORM\ManyToOne(targetEntity="Page")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="page_id")
     */
    private $parent;

    /**
     * @var bool $isHomePage
     *
     * @ORM\Column(name="is_home_page", type="boolean")
     */
    private $isHomePage;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Page|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Page|null $parent
     *
     * @return Page
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHomePage()
    {
        return $this->isHomePage;
    }

    /**
     * @param bool $isHomePage
     *
     * @return Page
     */
    public function setIsHomePage($isHomePage)
    {
        $this->isHomePage = $isHomePage;

        return $this;
    }
}