<?php

namespace Rialto\Accounting\Debtor\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Debtor\DebtorAllocation;
use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Accounting\PaymentTransaction\PaymentTransactionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DebtorAllocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var $alloc DebtorAllocation */
        $builder->add('amount', MoneyType::class, [
            'currency' => Currency::USD,
            'attr' => ['placeholder' => 'amount'],
        ]);

        /** @var $invoice DebtorInvoice */
        $invoice = $options['invoice'];
        $builder->add('credit', EntityType::class, [
            'class' => DebtorTransaction::class,
            'query_builder' => function (PaymentTransactionRepository $repo) use ($invoice) {
                return $repo->queryEligibleCreditsToMatch($invoice);
            },
            'choice_label' => 'summary',
            'placeholder' => '-- select a credit --',
            'mapped' => false,  // constructor argument
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $alloc = $event->getData();
            if (! $alloc ) {
                return;
            }
            $form = $event->getForm();
            $form->add('credit', EntityType::class, [
                'class' => DebtorTransaction::class,
                'choice_label' => 'summary',

                // Can't change the alloc's credit once it is set.
                'choices' => [$alloc->getCredit()],
                'mapped' => false,
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('invoice');
        $resolver->setAllowedTypes('invoice', DebtorInvoice::class);
        $resolver->setDefaults([
            'data_class' => DebtorAllocation::class,
            'empty_data' => function (FormInterface $form) {
                $invoice = $form->getConfig()->getOption('invoice');
                assertion($invoice instanceof DebtorInvoice);
                $credit = $form->get('credit')->getData();
                if ( $credit ) {
                    return new DebtorAllocation($invoice, $credit);
                }
                throw new TransformationFailedException("Credit is required");
            },
        ]);
    }
}
