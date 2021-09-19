<?php

namespace Rialto\Sales\Order\Web;

use Gumstix\FormBundle\Form\DynamicFormType;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Account\Orm\GLAccountRepository;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Orm\CustomizationRepository;
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


class SalesOrderDetailType extends DynamicFormType
{
    /**
     * @param SalesOrderDetail $orderItem
     */
    protected function updateForm(FormInterface $form, $orderItem)
    {
        $form
            ->add('qtyOrdered', IntegerType::class, [
            ])
            ->add('customerPartNo', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Customer part no.'],
            ])
            ->add('baseUnitPrice', MoneyType::class, [
                'currency' => Currency::USD,
                'scale' => SalesOrderDetail::UNIT_PRECISION,
            ])
            ->add('taxRate', PercentType::class, [
                'scale' => 2,
            ])
            ->add('discountAccount', EntityType::class, [
                'class' => GLAccount::class,
                'query_builder' => function (GLAccountRepository $repo) {
                    return $repo->querySalesAdjustments();
                },
            ])
            ->add('discountRate', PercentType::class, [
                'scale' => 2,
            ]);

        $stockItem = $orderItem->getStockItem();
        if ($stockItem->isVersioned()) {
            $form->add('version', VersionChoiceType::class, [
                'choices' => $stockItem->getVersions(),
                'allow_any' => true,
                'choice_label' => function ($value, $key, $index) {
                    if ($value instanceof ItemVersion) {
                        return $value->getCodeWithStatus();
                    }
                    return (string) $value;
                }
            ]);
        }
        if ($stockItem->isCustomizable()) {
            $form->add('customization', EntityType::class, [
                'class' => Customization::class,
                'query_builder' => function (CustomizationRepository $repo) use ($stockItem) {
                    return $repo->createBuilder()
                        ->bySku($stockItem)
                        ->getQueryBuilder();
                },
                'required' => false,
                'placeholder' => 'none',
            ]);

            $form->add('chargeForCustomizations', CheckboxType::class, [
                'required' => false,
                'label' => 'Charge for customizations?',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesOrderDetail::class
        ]);
    }
}


