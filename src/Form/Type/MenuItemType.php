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
            'data_class' => MenuItem::class,
        ]);

        parent::configureOptions($resolver);
    }
}