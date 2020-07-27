<?php

namespace AdminBundle\EventListener;

use AdminBundle\Entity\Menu;
use Doctrine\ORM\PersistentCollection;

final class MenuListener
{
    /**
     * @param Menu $entity
     */
    public function prePersist(Menu $entity)
    {
        $this->update($entity);
    }

    /**
     * @param Menu $entity
     */
    public function preUpdate(Menu $entity)
    {
        $this->update($entity);
    }

    /**
     * @param Menu $entity
     */
    private function update(Menu $entity)
    {
        $itemIds = $pageIds = [];

        /** @var PersistentCollection $items */
        $items     = $entity->getItems();
        $hierarchy = $this->getItemIds($items, $itemIds, $pageIds);

        $entity
            ->setItemIds($itemIds)
            ->setPageIds($pageIds)
            ->setHierarchy($hierarchy)
        ;
    }

    /**
     * @param PersistentCollection $items
     * @param array                $itemIds
     * @param array                $pageIds
     *
     * @return array
     */
    private function getItemIds(PersistentCollection $items, array &$itemIds, array &$pageIds)
    {
        if ($items->count() === 0) {
            return [];
        }

        $hierarchy = [];

        foreach ($items as $item) {
            $itemId = $item->getId();

            $itemIds[] = $itemId;

            if ($page = $item->getPage()) {
                $pageId = $page->getId();

                $pageIds[$pageId] = $itemId;
            }

            $hierarchy[$itemId] = $this->{__FUNCTION__}($item->getItems(), $itemIds, $pageIds);
        }

        return $hierarchy;
    }
}