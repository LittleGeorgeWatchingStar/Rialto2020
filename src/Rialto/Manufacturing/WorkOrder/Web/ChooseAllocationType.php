<?php

namespace Rialto\Manufacturing\WorkOrder\Web;

use Gumstix\FormBundle\Form\DynamicFormType;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Account\Orm\GLAccountRepository;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Orm\CustomizationRepository;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Web\VersionChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ChooseAllocationType extends DynamicFormType
{
    /**
     * @param SalesOrderDetail $orderItem
     */
    protected function updateForm(FormInterface $form, $orderItem)
    {
        $form->add('choose from', CheckboxType::class, [
        ])
            ->add('work order id', TextType::class, [
                'required' => true,
            ])
            ->add('qtyOrdered', IntegerType::class, [
            ])
            ->add('requested date', TextType::class, [
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WorkOrder::class
        ]);
    }
}


