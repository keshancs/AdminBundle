<?php

namespace AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Block
 *
 * @ORM\Entity
 * @ORM\Table(name="block")
 */
class Block
{
    /**
     * @var int $id
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="block_id", type="integer")
     */
    protected $id;

    /**
     * @var Page $page
     *
     * @ORM\ManyToOne(targetEntity="AdminBundle\Entity\Page", inversedBy="blocks")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="page_id")
     */
    protected $page;

    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string")
     */
    protected $type;

    /**
     * @var array $data
     *
     * @ORM\Column(name="data", type="json")
     */
    protected $data;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param Page $page
     *
     * @return Block
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Block
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return Block
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->type;
    }
}