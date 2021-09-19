<?php

namespace Rialto\Accounting\Debtor\Web;


use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Debtor\DebtorCredit;
use Rialto\Accounting\Debtor\OrderAllocation;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Web\Form\TextEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For allocating sales order to a debtor payment or credit.
 */
class OrderAllocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $alloc = $event->getData();
            $form = $event->getForm();
            $form
                ->add('salesOrder', TextEntityType::class, [
                    'class' => SalesOrder::class,
                    // Sales order is a constructor argument, so must be
                    // read-only for existing allocations.
                    'disabled' => $alloc && $alloc->getSalesOrder(),
                ])
                ->add('amount', MoneyType::class, [
                    'currency' => Currency::USD,
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('credit');
        $resolver->setAllowedTypes('credit', DebtorCredit::class);
        $resolver->setDefaults([
            'data_class' => OrderAllocation::class,
            'empty_data' => function (FormInterface $form) {
                $credit = $form->getConfig()->getOption('credit');
                assertion($credit instanceof DebtorCredit);
                $order = $form->get('salesOrder')->getData();
                if ($order) {
                    return new OrderAllocation($credit, $order);
                }
                throw new TransformationFailedException("Field 'salesOrder' is required");
            },
        ]);
    }
}
