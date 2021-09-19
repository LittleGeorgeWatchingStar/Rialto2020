<?php

namespace Rialto\Stock\Cost\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Accounting\Currency\Currency;
use Rialto\Stock\Cost\StandardCost;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for adding new standard cost records.
 */
class StandardCostType extends DynamicFormType
{
    /**
     * @param StandardCost $stdCost
     */
    protected function updateForm(FormInterface $form, $stdCost)
    {
        $form->add('materialCost', MoneyType::class, [
            'currency' => Currency::USD,
            'scale' => StandardCost::PRECISION,
            'label' => 'Material cost',
        ]);

        $item = $stdCost->getStockItem();
        if ( $item->isManufactured() ) {
            $form->add('labourCost', MoneyType::class, [
                'currency' => Currency::USD,
                'scale' => StandardCost::PRECISION,
                'label' => 'Labour cost',
            ]);
            $form->add('overheadCost', MoneyType::class, [
                'currency' => Currency::USD,
                'scale' => StandardCost::PRECISION,
                'label' => 'Overhead cost',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StandardCost::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'StandardCost';
    }
}
