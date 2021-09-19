<?php

namespace Rialto\Purchasing\Receiving\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Purchasing\Catalog\PurchasingData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form type for editing qtyToReverse records in GoodsReceivedItem.
 */
class GoodsReceivedItemAbstractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('qtyToReverse', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Range([
                        'min' => 1,
                        'minMessage' => 'Quantity to reverse must be positive.',
                    ]),
                    new Assert\Type(['type' => 'integer']),
                ],
            ]);
    }
}
