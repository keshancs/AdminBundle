<?php

namespace AdminBundle\Entity;

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
     * @var Page|null $translated
     *
     * @ORM\ManyToOne(targetEntity="Page")
     * @ORM\JoinColumn(name="translated_id", referencedColumnName="page_id")
     */
    private $translated;

    /**
     * @var string $locale
     *
     * @ORM\Column(name="locale", type="string")
     */
    private $locale;

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
     * @return Page|null
     */
    public function getTranslated()
    {
        return $this->translated;
    }

    /**
     * @param Page|null $translated
     *
     * @return Page
     */
    public function setTranslated($translated)
    {
        $this->translated = $translated;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return Page
     */
    public function setLocale(string $locale)
    {
        $this->locale = $locale;

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