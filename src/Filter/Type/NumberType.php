<?php

namespace AdminBundle\Filter\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType as BaseNumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NumberType extends BaseNumberType
{
    /**
     * @var string[]
     */
    protected $choices = [
        'Vienāds ar'         => 'eq',
        'Nav vienāds ar'     => 'neq',
        'Mazāks par'         => 'lt',
        'Lielāks par'        => 'gt',
        'Mazāks/vienāds ar'  => 'gte',
        'Lielāks/vienāds ar' => 'lte',
    ];

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'required'   => false,
                'attr'       => ['class' => 'select2bs4 form-control'],
                'choices'    => array_merge(['-' => ''], $this->choices),
                'data'       => $options['type'],
                'label'      => false,
                'row_attr'   => ['class' => 'mr-2 flex-fill'],
            ])
            ->add('value', null, [
                'attr'       => ['class' => 'form-control'],
                'label'      => false,
                'data'       => $options['value'],
                'row_attr'   => ['class' => 'flex-fill'],
            ])
        ;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'compound'      => true,
            'type'          => null,
            'value'         => null,
            'query_builder' => null,
        ]);
    }

    /**
     * @return string|null
     */
    public function getBlockPrefix()
    {
        return 'admin_number';
    }
}