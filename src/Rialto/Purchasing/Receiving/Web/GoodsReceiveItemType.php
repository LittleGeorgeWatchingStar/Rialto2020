<?php

namespace Rialto\Purchasing\Receiving\Web;

use Rialto\Purchasing\Catalog\CostBreak;
use Rialto\Purchasing\Receiving\GoodsReceivedItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing qtyToReverse records in GoodsReceivedItem.
 */
class GoodsReceiveItemType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GoodsReceivedItem::class,
        ]);
    }

    public function getParent()
    {
        return GoodsReceivedItemAbstractType::class;
    }
}
