<?php

namespace AdminBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="menu")
 * @ORM\EntityListeners({"AdminBundle\EventListener\MenuListener"})
 */
class Menu
{
    /**
     * @var int $id
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="menu_id", type="integer")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var ArrayCollection $pages
     *
     * @ORM\OneToMany(targetEntity="MenuItem", mappedBy="menu", cascade={"persist", "remove"})
     * @ORM\JoinColumn(referencedColumnName="menu_item_id")
     * @ORM\OrderBy({"priority" : "ASC"})
     */
    private $items;

    /**
     * @var array $itemIds
     *
     * @ORM\Column(name="item_ids", type="json", nullable=true)
     */
    private $itemIds = [];

    /**
     * @var array $pageIds
     *
     * @ORM\Column(name="page_ids", type="json", nullable=true)
     */
    private $pageIds = [];

    /**
     * @var array $hierarchy
     *
     * @ORM\Column(name="hierarchy", type="json", nullable=true)
     */
    private $hierarchy = [];

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Menu
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param ArrayCollection $items
     *
     * @return Menu
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @param MenuItem $item
     */
    public function addItem(MenuItem $item)
    {
        $this->items->add($item);

        $item->setMenu($this);
    }

    /**
     * @param MenuItem $item
     */
    public function removeItem(MenuItem $item)
    {
        $this->items->removeElement($item);

        $item->setMenu(null);
    }

    /**
     * @return array
     */
    public function getItemIds()
    {
        return $this->itemIds;
    }

    /**
     * @param array $itemIds
     *
     * @return Menu
     */
    public function setItemIds($itemIds)
    {
        $this->itemIds = $itemIds;

        return $this;
    }

    /**
     * @return array
     */
    public function getPageIds()
    {
        return $this->pageIds;
    }

    /**
     * @param array $pageIds
     *
     * @return Menu
     */
    public function setPageIds($pageIds)
    {
        $this->pageIds = $pageIds;

        return $this;
    }

    /**
     * @return array
     */
    public function getHierarchy()
    {
        return $this->hierarchy;
    }

    /**
     * @param array $hierarchy
     *
     * @return Menu
     */
    public function setHierarchy($hierarchy)
    {
        $this->hierarchy = $hierarchy;

        return $this;
    }
}