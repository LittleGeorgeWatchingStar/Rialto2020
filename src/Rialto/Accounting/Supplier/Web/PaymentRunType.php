<?php

namespace Rialto\Accounting\Supplier\Web;


use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Supplier\PaymentRun;
use Rialto\Time\Web\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentRunType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'PaymentRun';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('matching', TextType::class, [
                'label' => 'Suppliers matching',
                'required' => false,
                'attr' => ['placeholder' => 'all'],
        ])
            ->add('currency', EntityType::class, [
                'class' => Currency::class,
            ])
            ->add('dueUntil', DateType::class, [
                'label' => 'Payments due to',
            ])
            ->add('fromAccount', EntityType::class, [
                'class' => BankAccount::class,
                'label' => 'Pay from account',
            ])
            ->add('paymentType', ChoiceType::class, [
                'choices' => BankTransaction::getValidPaymentTypes(),
                'label' => 'Payment method'
            ])
            ->add('chequeNumber', IntegerType::class, [
                'label' => 'Starting cheque no',
            ])
            ->add('preview', SubmitType::class)
            ->add('pdf', SubmitType::class, [
                'label' => 'Preview PDF',
            ])
            ->add('zip', SubmitType::class, [
                'label' => 'Download Zip of All Invoices',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentRun::class,
        ]);
    }
}
