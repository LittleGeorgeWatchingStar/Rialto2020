<?php

namespace Rialto\Purchasing\Catalog\Web;

use Rialto\Purchasing\Catalog\CostBreak;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing CostBreak records.
 */
class CostBreakType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CostBreak::class,
        ]);
    }

    public function getParent()
    {
        return CostBreakAbstractType::class;
    }
}
