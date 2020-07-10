<?php

namespace AdminBundle\Admin;

use AdminBundle\Controller\PageController;
use AdminBundle\Entity\Page;
use AdminBundle\Mapper\FormMapper;
use AdminBundle\Mapper\ListMapper;
use AdminBundle\Routing\Router;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Regex;
use Twig\Environment;

class PageAdmin extends AbstractAdmin
{
    /**
     * @inheritDoc
     */
    public function configure(Environment $environment, Request $request)
    {
        parent::configure($environment, $request);

        $this->getContext()->setShowPageSidebar(true);
    }

    /**
     * @inheritDoc
     */
    public function createQuery()
    {
        $qb = parent::createQuery();
        $qb
            ->andWhere($qb->expr()->eq('o.locale', ':locale'))
            ->setParameter('locale', 'lv')
        ;

        return $qb;
    }

    /**
     * @inheritDoc
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('title', [
                'template'   => '@Admin/CRUD/page_list.html.twig',
                'block_name' => 'title'
            ])
            ->add('locale', [
                'template'   => '@Admin/CRUD/page_list.html.twig',
                'block_name' => 'locale'
            ])
        ;
    }

    /**
     * @inheritDoc
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        /** @var Page $object */
        $object        = $this->getSubject();
        $isTranslation = $object->getId() && $object->getLocale() !== 'lv';
        $frontLocales  = $this->getSettingManager()->get('front_locales');

        $formMapper
            ->beginTab('general')
                ->add('isHomePage', CheckboxType::class, [
                    'required' => false,
                ])
                ->add('isManualSlug', CheckboxType::class, [
                    'required' => false,
                    'row_attr' => $object->isHomePage() ? ['style' => 'display: none'] : [],
                ])
                ->add('slug', TextType::class, [
                    'required'    => !$object->getIsManualSlug(),
                    'disabled'    => !$object->getIsManualSlug() || $object->isHomePage(),
                    'row_attr'    => $object->isHomePage() ? ['style' => 'display: none'] : [],
                    'constraints' => [
                        new Regex(['pattern' => '/^[a-zA-Z0-9-]+$/']),
                    ],
                ])
        ;

        if ($isTranslation) {
            $formMapper
                ->add('original', EntityType::class, [
                    'required'      => $isTranslation,
                    'attr'          => $object->isHomePage() ? ['style' => 'display: none'] : ['class' => 'select2bs4 form-control'],
                    'class'         => $this->getClass(),
                    'disabled'      => $object->getId() && $object->getLocale() !== 'lv',
                    'help'          => 'admin.form.page.help.label_original',
                    'row_attr'      => ['class' => 'form-group'],
                    'query_builder' => function (EntityRepository $repository) {
                        $qb = $repository->createQueryBuilder('o');
                        $qb
                            ->select('o')
                            ->where($qb->expr()->isNull('o.parent'))
                        ;

                        return $qb;
                    },
                ])
            ;
        } else {
            $formMapper
                ->add('parent', EntityType::class, [
                    'required'      => false,
                    'attr'          => $object->isHomePage() ? ['style' => 'display: none'] : ['class' => 'select2bs4 form-control'],
                    'class'         => $this->getClass(),
                    'row_attr'      => ['class' => 'form-group'],
                    'query_builder' => function (EntityRepository $repository) use ($object) {
                        $qb = $repository->createQueryBuilder('o');
                        $qb
                            ->select('o')
                            ->where($qb->expr()->neq('o.id', ':page_id'))
                            ->setParameter('page_id', $object->getId())
                        ;

                        return $qb;
                    },
                ])
            ;
        }

        $formMapper
            ->add('locale', ChoiceType::class, [
                'attr'     => ['class' => 'select2bs4 form-control'],
                'choices'  => array_combine($frontLocales, $frontLocales),
                'disabled' => $object->getId() && $object->getLocale() !== 'lv',
                'required' => true,
            ])
            ->add('title', null, [
                'required' => true,
                'attr'     => ['class' => 'form-control'],
            ])
            ->add('menuTitle', null, [
                'required' => false,
                'attr'     => ['class' => 'form-control'],
            ])
        ;

        $formMapper->endTab();

        $formMapper
            ->beginTab('seo')
                ->add('seoTitle', null, ['required' => false])
                ->add('seoKeywords', TextareaType::class, ['required' => false])
                ->add('seoDescription', TextareaType::class, ['required' => false])
            ->endTab()
        ;
    }

    /**
     * @param Router $router
     */
    public function configureRoutes(Router $router)
    {
        $router
            ->add(
                'create_from_tree',
                'create_from_tree',
                ['_controller' => PageController::class . '::createFromTree'],
                [],
                [],
                '',
                [],
                ['POST']
            )
            ->add(
                'update_from_tree',
                'update_from_tree',
                ['_controller' => PageController::class . '::updateFromTree'],
                [],
                [],
                '',
                [],
                ['POST']
            )
        ;
    }
}