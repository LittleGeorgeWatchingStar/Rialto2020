<?php

namespace Rialto\Sales\Price\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Sales\Price\ProductPrice;
use Rialto\Sales\Type\SalesType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing ProductPrice records.
 */
class ProductPriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('salesType', EntityType::class, [
            'class' => SalesType::class,
            'required' => false,
            'label' => 'Sales type',
        ])
        ->add('currency', EntityType::class, [
            'class' => Currency::class,
        ])
        ->add('price', NumberType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductPrice::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ProductPrice';
    }

}
