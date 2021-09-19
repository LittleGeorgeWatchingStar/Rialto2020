<?php

namespace Rialto\Sales\Customer\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Terms\PaymentTerms;
use Rialto\Geography\Address\Web\AddressEntityType;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Type\SalesType;
use Rialto\Tax\TaxExemption;
use Rialto\Time\Web\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerType
    extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'label' => 'Customer name',
        ])
        ->add('companyName', TextType::class, [
            'label' => 'Company name',
        ])
        ->add('address', AddressEntityType::class, [
            'label' => 'Address',
        ])
        ->add('email', EmailType::class, [
            'label' => 'Email',
        ])
        ->add('salesType', EntityType::class, [
            'label' => 'Sales type/price list',
            'class' => SalesType::class,
        ])
        ->add('customerSince', DateTimeType::class, [
            'label' => 'Customer since',
            'invalid_message' => 'Invalid date for "customer since"',
        ])
        ->add('taxId', TextType::class, [
            'label' => 'Tax ID',
            'required' => false,
        ])
        ->add('taxExemptionNumber', TextType::class, [
            'label' => 'Tax exemption no',
            'required' => false,
        ])
        ->add('taxExemptionStatus', ChoiceType::class, [
            'label' => 'Tax exemption status',
            'choices' => TaxExemption::getChoices(),
        ])
        ->add('discountRate', PercentType::class, [
            'label' => 'Discount percent',
        ])
        ->add('creditLimit', MoneyType::class, [
            'currency' => Currency::USD,
            'label' => 'Credit limit',
            'required' => false,
        ])
        ->add('paymentTerms', EntityType::class, [
            'label' => 'Payment terms',
            'class' => PaymentTerms::class,
        ])
        ->add('currency', EntityType::class, [
            'label' => 'Customer currency',
            'class' => Currency::class,
        ])
        ->add('addressedAtBranch', ChoiceType::class, [
            'label' => 'Invoice addressing',
            'choices' => [
                'Address to HO' => 0,
                'Address to Branch' => 1,
            ],
        ])
        ->add('internalCustomer', CheckboxType::class, [
            'required' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Customer';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
