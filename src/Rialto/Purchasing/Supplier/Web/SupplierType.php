<?php

namespace Rialto\Purchasing\Supplier\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Terms\PaymentTerms;
use Rialto\Geography\Address\Web\AddressEntityType;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Tax\Authority\TaxAuthority;
use Rialto\Web\Form\JsEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing a Supplier record.
 */
class SupplierType extends AbstractType
{
    const START_YEAR = 1970;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $thisYear = (int) date('Y');

        $builder
            ->add('name', TextType::class, [
                'attr' => ['class' => 'name']
            ])
            ->add('orderAddress', AddressEntityType::class, [
                'label' => 'Order address',
                'attr' => ['class' => 'address'],
                'required' => false,
            ])
            ->add('paymentAddress', AddressEntityType::class, [
                'label' => 'Payment address',
                'attr' => ['class' => 'address'],
                'required' => false,
            ])
            ->add('supplierSince', DateType::class, [
                'label' => 'Supplier since',
                'years' => range(self::START_YEAR, $thisYear),
                'required' => false,
            ])
            ->add('customerAccount', TextType::class, [
                'label' => 'Our account number',
                'required' => false,
            ])
            ->add('customerNumber', TextType::class, [
                'label' => 'Secondary account number',
                'required' => false,
            ])
            ->add('website', UrlType::class, [
                'required' => false,
            ])
            ->add('bankParticulars', TextType::class, [
                'label' => 'Bank particulars',
                'required' => false,
            ])
            ->add('bankReference', TextType::class, [
                'label' => 'Bank reference',
                'required' => false,
            ])
            ->add('bankAccount', TextType::class, [
                'label' => 'Bank account',
                'required' => false,
            ])
            ->add('paymentTerms', EntityType::class, [
                'label' => 'Payment terms',
                'class' => PaymentTerms::class,
                'choice_label' => 'name',
            ])
            ->add('currency', EntityType::class, [
                'class' => Currency::class,
                'choice_label' => 'name',
            ])
            ->add('remittanceAdviceRequired', CheckboxType::class, [
                'label' => 'Remittance advice required?',
                'required' => false,
            ])
            ->add('taxAuthority', EntityType::class, [
                'label' => 'Tax authority',
                'class' => TaxAuthority::class,
                'choice_label' => 'description',
            ])
            ->add('parent', JsEntityType::class, [
                'class' => Supplier::class,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Supplier::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Supplier';
    }

}
