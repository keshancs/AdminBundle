<?php

namespace AdminBundle\Admin;

use AdminBundle\Mapper\ListMapper;

class UserAdmin extends AbstractAdmin
{
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('username')
            ->add('roles', [
                'template'   => '@Admin/CRUD/user_list.html.twig',
                'block_name' => 'roles',
            ])
        ;
    }
}