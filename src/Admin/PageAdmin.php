<?php

namespace AdminBundle\Admin;

use AdminBundle\Entity\Setting;
use AdminBundle\Mapper\FormMapper;
use AdminBundle\Mapper\ListMapper;
use AdminBundle\Transformer\LocaleTransformer;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PageAdmin extends AbstractAdmin
{
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
            ->add('title')
            ->add('path')
            ->add('locale', ['widget' => 'page_locale'])
        ;
    }

    /**
     * @inheritDoc
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $object        = $this->getSubject();
        $isTranslation = $object->getId() && $object->getLocale() !== 'lv';
        $frontLocales  = $this->getSettingManager()->get('front_locales');

        $formMapper
            ->beginTab('general')
                ->add('parent', EntityType::class, [
                    'required'      => false,
                    'attr'          => ['class' => 'select2bs4 form-control'],
                    'class'         => $this->getClass(),
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
                ->add('original', EntityType::class, [
                    'required'      => $isTranslation,
                    'attr'          => ['class' => 'select2bs4 form-control'],
                    'class'         => $this->getClass(),
                    'disabled'      => $object->getId() && $object->getLocale() !== 'lv',
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
            ->endTab()
            ->beginTab('seo')
                ->add('seoTitle', null, ['required' => false])
                ->add('seoKeywords', TextareaType::class, ['required' => false])
                ->add('seoDescription', TextareaType::class, ['required' => false])
            ->endTab()
        ;
    }
}