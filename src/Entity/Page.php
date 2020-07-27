<?php

namespace AdminBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="page")
 * @ORM\EntityListeners({"AdminBundle\EventListener\PageListener"})
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
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="page_id", nullable=true)
     */
    private $parent;

    /**
     * @var Page|null $parent
     *
     * @ORM\OneToMany(targetEntity="Page", mappedBy="parent")
     * @ORM\JoinColumn(referencedColumnName="page_id")
     */
    private $children;

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
     * @var string $menuTitle
     *
     * @ORM\Column(name="menu_title", type="string", nullable=true)
     */
    private $menuTitle;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string")
     */
    private $slug;

    /**
     * @var string $isManualSlug
     *
     * @ORM\Column(name="is_manual_slug", type="boolean")
     */
    private $isManualSlug;

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

    /**
     * @var int $priority
     *
     * @ORM\Column(name="priority", type="string", nullable=true)
     */
    private $priority;

    /**
     * @var array $hierarchy
     *
     * @ORM\Column(name="hierarchy", type="json")
     */
    private $hierarchy;

    /**
     * @var ArrayCollection $blocks
     *
     * @ORM\OneToMany(targetEntity="AdminBundle\Entity\Block", mappedBy="page")
     * @ORM\JoinColumn(referencedColumnName="block_id")
     */
    private $blocks;

    public function __construct()
    {
        $this->isManualSlug = false;
        $this->children     = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->blocks       = new ArrayCollection();
        $this->hierarchy    = [];
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

        // TODO: move into separate method
        if ($parent) {
            while ($parent) {
                array_unshift($this->hierarchy, $parent->getId());

                $parent = $parent->getParent();
            }
        }

        return $this;
    }

    /**
     * @return Page|null
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Page|null $children
     *
     * @return Page
     */
    public function setChildren($children)
    {
        $this->children = $children;

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
     * @param string $locale
     *
     * @return Page|null
     */
    public function getTranslation(string $locale)
    {
        return $this->translations->filter(function (Page $translation) use ($locale) {
            return $translation->getLocale() === $locale;
        })->first();
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
    public function getMenuTitle()
    {
        return $this->menuTitle;
    }

    /**
     * @param string $menuTitle
     *
     * @return Page
     */
    public function setMenuTitle($menuTitle)
    {
        $this->menuTitle = $menuTitle;

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
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getIsManualSlug()
    {
        return $this->isManualSlug;
    }

    /**
     * @param string $isManualSlug
     *
     * @return Page
     */
    public function setIsManualSlug($isManualSlug)
    {
        $this->isManualSlug = $isManualSlug;

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
    public function setPath($path)
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
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return Page
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

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
     * @return Page
     */
    public function setHierarchy($hierarchy)
    {
        $this->hierarchy = $hierarchy;

        return $this;
    }

    /**
     * @return string
     */
    public function getCollectionTitle()
    {
        return $this->menuTitle ?: $this->title;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->seoTitle ?? $this->title;
    }

    /**
     * Clone
     */
    public function __clone()
    {
        $this->id = null;
    }
}
