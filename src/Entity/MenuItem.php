<?php

namespace AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="menu_item")
 */
class MenuItem
{
    const TYPE_PAGE = 1;
    const TYPE_URL  = 2;

    /**
     * @var int $id
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="menu_item_id", type="integer")
     */
    private $id;

    /**
     * @var Menu $menu
     *
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="items")
     * @ORM\JoinColumn(fieldName="menu_id", referencedColumnName="menu_id")
     */
    private $menu;

    /**
     * @var int $type
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * @var Page|null $page
     *
     * @ORM\ManyToOne(targetEntity="Page")
     * @ORM\JoinColumn(fieldName="page_id", referencedColumnName="page_id", nullable=true)
     */
    private $page;

    /**
     * @var string|null $url
     *
     * @ORM\Column(name="url", type="string", nullable=true)
     */
    private $url;

    /**
     * @var int $priority
     *
     * @ORM\Column(name="priority", type="integer")
     */
    private $priority;

    public function __construct()
    {
        $this->type = self::TYPE_PAGE;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @param Menu $menu
     *
     * @return MenuItem
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return MenuItem
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Page|null
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param Page|null $page
     *
     * @return MenuItem
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     *
     * @return MenuItem
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return MenuItem
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }
}