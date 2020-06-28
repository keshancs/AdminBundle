<?php

namespace AdminBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CollectionType as BaseCollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollectionType extends BaseCollectionType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'sortable' => false,
        ]);

        parent::configureOptions($resolver);
    }
}