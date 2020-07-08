<?php

namespace AdminBundle\Form\Type;

use AdminBundle\Entity\MenuItem;
use AdminBundle\Entity\Page;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuItemType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $nestingLevel = $options['nesting_level'];

        $builder
            ->add('type', ChoiceType::class, [
                'attr'     => ['class' => 'select2bs4 form-control'],
                'choices'  => [
                    'admin.form.menu_item.label_page' => MenuItem::TYPE_PAGE,
                    'admin.form.menu_item.label_url'  => MenuItem::TYPE_URL,
                ],
                'label'    => 'admin.form.menu_item.label_type',
                'row_attr' => ['class' => 'mr-1 js-menu-item-type', 'style' => 'width: 100px'],
            ])
        ;

        if ($nestingLevel + 1 < $options['max_nesting_level']) {
            $builder
                ->add('items', CollectionType::class, [
                    'label'         => 'admin.form.menu.label_items',
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'attr'          => ['class' => 'collection list-group'],
                    'by_reference'  => false,
                    'entry_options' => ['label' => false, 'nesting_level' => $nestingLevel + 1],
                    'entry_type'    => MenuItemType::class,
                    'sortable'      => true,
                ])
            ;
        }

        $builder
            ->add('priority', HiddenType::class, [
                'attr'  => ['class' => 'js-menu-item-priority'],
                'label' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder) {
                $form    = $event->getForm();
                /** @var MenuItem $object */
                $object  = $event->getData();
                $isPage  = !$object instanceof MenuItem || $object->getType() == MenuItem::TYPE_PAGE;

                $form
                    ->add('page', EntityType::class, [
                        'attr'          => ['class' => 'select2bs4 form-control'],
                        'class'         => Page::class,
                        'choice_label'  => 'collectionTitle',
                        'label'         => 'admin.form.menu_item.label_page',
                        'placeholder'   => 'admin.form.placeholder.label_select',
                        'required'      => $isPage,
                        'row_attr'      => [
                            'class' => 'flex-fill mr-1 js-menu-item-page',
                            'style' => !$object || $isPage ? '' : 'display: none',
                        ],
                        'query_builder' => function (EntityRepository $repo) {
                            $qb = $repo->createQueryBuilder('o');

                            return $qb->select('o')
                                ->where($qb->expr()->eq('o.locale', ':locale'))
                                ->setParameter('locale', 'lv');
                        },
                    ])
                    ->add('url', TextType::class, [
                        'attr'     => ['class' => 'form-control'],
                        'label'    => 'admin.form.menu_item.label_url',
                        'required' => !$isPage,
                        'row_attr' => [
                            'class' => 'flex-fill mr-1 js-menu-item-url',
                            'style' => !$object || $isPage ? 'display: none' : '',
                        ],
                    ])
                ;
            });
        ;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => MenuItem::class,
            'nesting_level'      => 0,
            'max_nesting_level'  => 10,
        ]);
    }
}