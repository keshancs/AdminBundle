<?php

namespace AdminBundle\Block;

use AdminBundle\Entity\Block;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;
use Twig\Error\Error;

class TextBlockType extends AbstractType implements BlockInterface
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class, [
                'attr'     => ['class' => 'form-control'],
                'row_attr' => ['class' => 'form-group'],
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Block::class,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function render(Environment $twig, Block $block)
    {
        try {
            return $twig->render('@Admin/CRUD/text_block.html.twig', [
                'block' => $block,
            ]);
        } catch (Error $e) {
        }

        return null;
    }
}