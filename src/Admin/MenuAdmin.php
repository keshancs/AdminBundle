<?php

namespace AdminBundle\Admin;

use AdminBundle\Form\Type\CollectionType;
use AdminBundle\Form\Type\MenuItemType;
use AdminBundle\Mapper\FormMapper;
use AdminBundle\Mapper\ListMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MenuAdmin extends AbstractAdmin
{
    /**
     * @inheritDoc
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class, [
                'help' => 'admin.form.menu.help.label_name',
            ])
            ->add('items', CollectionType::class, [
                'label'         => 'admin.form.menu.label_items',
                'allow_add'     => true,
                'allow_delete'  => true,
                'attr'          => ['class' => 'collection list-group'],
                'by_reference'  => false,
                'entry_options' => ['label' => false],
                'entry_type'    => MenuItemType::class,
                'sortable'      => true,
            ])
        ;
    }

    /**
     * @inheritDoc
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('name')
        ;
    }
}