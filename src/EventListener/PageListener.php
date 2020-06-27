<?php

namespace AdminBundle\EventListener;

use AdminBundle\Entity\Page;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Transliterator;

final class PageListener
{
    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @param string $defaultLocale
     */
    public function __construct(string $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param Page $entity
     */
    public function preUpdate(Page $entity)
    {
        $transliterator = Transliterator::create(
            'Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();'
        );

        // TODO: maybe transliterator can handle that - investigate
        // Remove unwanted characters
        $slug = preg_replace('/[^a-zA-Z0-9\s]/', '', $transliterator->transliterate($entity->getTitle()));
        // Replace more than one spaces with one
        $slug = preg_replace('/\s+/', ' ', $slug);
        // Replace multi-dash occurrences with single-dash
        $slug = preg_replace('/-+/', '-', $slug);

        $entity->setSlug($slug);

        while ($parent = $entity->getParent()) {
            $slug = $entity->getSlug() . '/' . $slug;
        }

        $path = ($this->defaultLocale === $entity->getLocale() ? '' : '/' . $entity->getLocale()) . '/' . $slug;

        $entity->setPath($path);
    }
}