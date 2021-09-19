<?php

namespace Rialto\Sales\Discount\Web;

use Rialto\Sales\Discount\DiscountRate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiscountRateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('threshold', IntegerType::class, [
                'required' => false,
            ])
            ->add('discountRate', PercentType::class, [
                'required' => false,
            ])
            ->add('discountRateRelated', PercentType::class, [
                'required' => false,
            ]);
    }

    public function getBlockPrefix()
    {
        return 'DiscountRate';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DiscountRate::class
        ]);
    }
}
