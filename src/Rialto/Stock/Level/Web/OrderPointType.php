<?php

namespace Rialto\Stock\Level\Web;

use Rialto\Stock\Level\StockLevelStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For editing the order point for an item at a specific location.
 */
class OrderPointType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('orderPoint', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockLevelStatus::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'StockLevelStatus';
    }

}
