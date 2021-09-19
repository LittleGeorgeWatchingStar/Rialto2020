<?php

namespace Rialto\Stock\Cost\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Stock\Cost\StandardCost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StandardCostApiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('materialCost', MoneyType::class, [
            'scale' => StandardCost::PRECISION,
            'currency' => Currency::USD
        ]);
        $builder->add('labourCost', MoneyType::class, [
            'scale' => StandardCost::PRECISION,
            'currency' => Currency::USD
        ]);
        $builder->add('overheadCost', MoneyType::class, [
            'scale' => StandardCost::PRECISION,
            'currency' => Currency::USD
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StandardCost::class,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'StandardCost';
    }

}
