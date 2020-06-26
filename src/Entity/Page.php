<?php

namespace AdminBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="page_id", nullable=true)
     */
    private $parent;

    /**
     * @var Page|null $translation
     *
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="translations")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="page_id", nullable=true)
     */
    private $original;

    /**
     * @var ArrayCollection $translations
     *
     * @ORM\OneToMany(targetEntity="Page", mappedBy="original", indexBy="locale")
     * @ORM\JoinColumn(name="translation_id", referencedColumnName="page_id", nullable=true)
     */
    private $translations;

    /**
     * @var string $locale
     *
     * @ORM\Column(name="locale", type="string")
     */
    private $locale;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string")
     */
    private $title;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string")
     */
    private $slug;

    /**
     * @var string $path
     *
     * @ORM\Column(name="path", type="string")
     */
    private $path;

    /**
     * @var string $seoTitle
     *
     * @ORM\Column(name="seo_title", type="string", nullable=true)
     */
    private $seoTitle;

    /**
     * @var string $seoKeywords
     *
     * @ORM\Column(name="seo_keywords", type="string", nullable=true)
     */
    private $seoKeywords;

    /**
     * @var string $seoDescription
     *
     * @ORM\Column(name="seo_description", type="string", nullable=true)
     */
    private $seoDescription;

    /**
     * @var bool $isHomePage
     *
     * @ORM\Column(name="is_home_page", type="boolean")
     */
    private $isHomePage;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

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
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * @param Page|null $original
     *
     * @return Page
     */
    public function setOriginal($original)
    {
        $this->original = $original;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param ArrayCollection $translations
     *
     * @return Page
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;

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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Page
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return Page
     */
    public function setSlug(string $slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return Page
     */
    public function setPath(string $path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @param string|null $seoTitle
     *
     * @return Page
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSeoKeywords()
    {
        return $this->seoKeywords;
    }

    /**
     * @param string|null $seoKeywords
     *
     * @return Page
     */
    public function setSeoKeywords($seoKeywords)
    {
        $this->seoKeywords = $seoKeywords;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    /**
     * @param string|null $seoDescription
     *
     * @return Page
     */
    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;

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

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s [%d]', $this->seoTitle ?? $this->title, $this->id);
    }

    /**
     * Clone
     */
    public function __clone()
    {
        $this->id = null;
    }
}