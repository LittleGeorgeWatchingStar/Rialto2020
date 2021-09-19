<?php

namespace Rialto\Accounting\Bank\Statement\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Accounting\Bank\Statement\BankStatementPattern;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Web\Form\JsEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing bank statement patterns.
 *
 * @see BankStatementPattern
 */
class BankStatementPatternType extends DynamicFormType
{
    public function getBlockPrefix()
    {
        return 'BankStatementPattern';
    }

    /**
     * @param BankStatementPattern $pattern
     */
    protected function updateForm(FormInterface $form, $pattern)
    {
        $strategy = $pattern->getStrategy();
        $form->add('statementPattern', TextType::class, [
            'label' => 'Statement Pattern: determines the statement lines to which this pattern will apply',
        ]);
        $form->add('additionalStatementPattern', TextType::class, [
            'label' => 'Additional Statement Pattern: matching statement lines will be grouped along with this one',
            'required' => false,
        ]);
        $form->add('additionalStatementDateConstraint', IntegerType::class, [
            'label' => 'Additional Statement Date Constraint: additional statements whose date differs by more than this will not be grouped along with this one',
            'required' => false,
        ]);
        if ($strategy == "BankTransaction" || $strategy == "ExistingSupplierInvoice") {
            $form->add('referencePattern', TextType::class, [
                'label' => 'Reference Pattern: transactions matching this pattern will be considered potential matches',
                'required' => false,
            ]);
        }
        if ($strategy == "BankTransaction" || $strategy == "Cheque" || $strategy == "CustomerRefund") {
            $form->add('amountConstraint', NumberType::class, [
                'label' => 'Amount Constraint: transactions whose amount differs by more than this will not be considered potential matches',
                'required' => false,
            ]);
        }
        if ($strategy == "BankTransaction" || $strategy == "Cheque" || $strategy == "ExistingSupplierInvoice") {
            $form->add('dateConstraint', IntegerType::class, [
                'label' => 'Date Constraint: transactions whose date differs by more than this will not be considered potential matches',
            ]);
        }
        if ( $this->isSupplierStrategy($strategy) || $strategy == "BankTransaction" ) {
            $form->add('adjustmentAccount', EntityType::class, [
                'label' => 'Adjustment Account: adjustments to the transaction amount will be made against this account',
                'required' => false,
                'class' => GLAccount::class,
            ]);
        }
        if ( $this->isSupplierStrategy($strategy) ) {
            $form->add('supplier', JsEntityType::class, [
                'label' => 'Supplier: new transactions will be created against this supplier',
                'required' => false,
                'class' => Supplier::class,
                'property' => 'name',
            ]);
        }
        if ($strategy == "ExistingSupplierInvoice" || $strategy == "BankTransaction") {
            $form->add('updatePattern', TextType::class, [
                'label' => 'Update Pattern: determines which transactions can be adjusted to match the statement',
                'required' => false,
            ]);
        }
        $form->add('sortOrder', IntegerType::class, [
            'label' => 'Sort Order: lower numbers will have higher priority when matching',
            'required' => false,
        ]);
    }

    private function isSupplierStrategy($strategy)
    {
        return stripos($strategy, 'supplier') !== false;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BankStatementPattern::class,
        ]);
    }
}


