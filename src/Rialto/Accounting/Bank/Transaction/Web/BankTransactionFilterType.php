<?php

namespace Rialto\Accounting\Bank\Transaction\Web;


use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BankTransactionFilterType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => BankTransaction::getValidPaymentTypes(),
                'required' => false,
                'placeholder' => '-- all --',
                'label' => 'Payment type',
            ])
            ->add('systemType', EntityType::class, [
                'class' => SystemType::class,
                'required' => false,
                'placeholder' => '-- all --',
                'label' => 'Transaction type',
            ])
            ->add('bankAccount', EntityType::class, [
                'class' => BankAccount::class,
                'required' => false,
                'placeholder' => '-- any --',
            ])
            ->add('supplier', JsEntityType::class, [
                'class' => Supplier::class,
                'required' => false,
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Between',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'label' => 'and',
                'required' => false,
            ])
            ->add('memo', TextType::class, [
                'required' => false,
            ])
            ->add('cleared', YesNoAnyType::class, [
                'label' => 'Cleared?',
            ])
            ->add('filter', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return FilterForm::class;
    }
}
