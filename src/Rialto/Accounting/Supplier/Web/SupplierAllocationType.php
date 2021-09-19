<?php

namespace Rialto\Accounting\Supplier\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Supplier\SupplierAllocation;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Accounting\Supplier\SupplierTransactionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierAllocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('amount', MoneyType::class, [
            'currency' => Currency::USD,
            'attr' => ['placeholder' => 'amount'],
        ]);

        /** @var $invoice SupplierTransaction */
        $invoice = $options['invoice'];
        $builder->add('credit', EntityType::class, [
            'class' => SupplierTransaction::class,
            'query_builder' => function (SupplierTransactionRepository $repo) use ($invoice) {
                return $repo->queryEligibleCreditsToMatch($invoice);
            },
            'choice_label' => 'summary',
            'placeholder' => '-- choose credit --',
            'mapped' => false,  // constructor argument
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /* @var $alloc SupplierAllocation */
            $alloc = $event->getData();
            if (!$alloc) {
                return;
            }
            $form = $event->getForm();
            $form->add('credit', EntityType::class, [
                'class' => SupplierTransaction::class,
                'choice_label' => 'summary',

                // Once set, the credit cannot be changed.
                'choices' => [$alloc->getCredit()],
                'mapped' => false,
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('invoice');
        $resolver->setAllowedTypes('invoice', SupplierTransaction::class);
        $resolver->setDefaults([
            'data_class' => SupplierAllocation::class,
            'empty_data' => function (FormInterface $form) {
                $invoice = $form->getConfig()->getOption('invoice');
                assertion($invoice instanceof SupplierTransaction);
                $credit = $form->get('credit')->getData();
                if ($credit) {
                    return new SupplierAllocation($invoice, $credit);
                }
                throw new TransformationFailedException("Credit is required");
            },
        ]);
    }
}
