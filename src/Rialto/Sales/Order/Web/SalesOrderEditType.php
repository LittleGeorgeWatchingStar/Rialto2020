<?php

namespace Rialto\Sales\Order\Web;

use Rialto\Accounting\Currency\Currency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * For editing an existing sales order.
 */
class SalesOrderEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('lineItems', CollectionType::class, [
            'entry_type' => SalesOrderDetailType::class,
            'by_reference' => false,
            'label' => false,
        ]);
        $builder->add('depositAmount', MoneyType::class, [
            'currency' => Currency::USD,
            'label' => 'Confirmation payment',
        ]);

        $builder->add('addNewItem', SubmitType::class);
    }

    public function getParent()
    {
        return SalesOrderType::class;
    }

    public function getBlockPrefix()
    {
        return 'SalesOrderEdit';
    }
}
