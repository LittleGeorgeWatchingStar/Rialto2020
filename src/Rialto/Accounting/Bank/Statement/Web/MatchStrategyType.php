<?php

namespace Rialto\Accounting\Bank\Statement\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Accounting\Bank\Statement\BankStatement;
use Rialto\Accounting\Bank\Statement\Match as Strategy;
use Rialto\Accounting\Bank\Statement\Match\MatchStrategy;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For matching off a single bank statement against some existing transaction.
 *
 * As you can see below, the contents of the form depend on which matching
 * strategy is in use.
 */
class MatchStrategyType extends DynamicFormType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MatchStrategy::class,
            'label' => ' ',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'MatchStrategy';
    }

    /**
     * @param MatchStrategy $strategy
     */
    protected function updateForm(FormInterface $form, $strategy)
    {
        $additionalStatements = $strategy->getAdditionalStatements();
        if ( count($additionalStatements) ) {
            $form->add('acceptedStatements', EntityType::class, [
                'class' => BankStatement::class,
                'choices' => $additionalStatements,
                'choice_label' => 'id',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => ' ',
            ]);
        }

        $bankTrans = $strategy->getMatchingBankTransactions();
        if (! empty($bankTrans) ) {
            $form->add('acceptedBankTransactions', EntityType::class, [
                'class' => BankTransaction::class,
                'choices' => $bankTrans,
                'choice_label' => 'id',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => ' ',
            ]);
        }
        if ( $strategy instanceof Strategy\ExistingSupplierInvoiceStrategy )
        {
            $suppTrans = $strategy->getMatchingSupplierInvoices();
            $form->add('acceptedSupplierInvoices', EntityType::class, [
                'class' => SupplierTransaction::class,
                'choices' => $suppTrans,
                'choice_label' => 'id',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => ' ',
            ]);
        }
        elseif ( $strategy instanceof Strategy\CreateSupplierInvoiceStrategy )
        {
            $suppName = $strategy->getSupplierName();
            $form->add('createInvoices', ChoiceType::class, [
                'label' => "Create for $suppName:",
                'required' => false,
                'choices' => [
                    'invoice and payment' => Strategy\CreateSupplierInvoiceStrategy::INVOICES,
                    'payment only' => Strategy\CreateSupplierInvoiceStrategy::PAYMENTS,
                ],
                'expanded' => true,
            ]);
        }
        elseif ( $strategy instanceof Strategy\CustomerPrepaymentStrategy ) {
            $matchingOrders = $strategy->getMatchingOrders();
            $form->add('acceptedOrders', EntityType::class, [
                'class' => SalesOrder::class,
                'choices' => $matchingOrders,
                'choice_label' => 'id',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ]);

            $form->add('transferFee', MoneyType::class, [
                'currency' => Currency::USD,
                'label' => 'Transfer fee',
                'required' => false,
            ]);
            $form->add('sendEmail', ChoiceType::class, [
                'choices' => $this->getEmailNotificationChoices($matchingOrders),
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ]);
        }
        elseif ( $strategy instanceof Strategy\CustomerOverpaymentStrategy ) {
            $form->add('selectedCustomer', EntityType::class, [
                'class' => Customer::class,
                'choices' => $strategy->getMatchingCustomers(),
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => true,
                'required' => false,
                'attr' => ['class' => 'checkbox_group'],
            ]);
        }
    }

    /**
     * @param SalesOrder[] $salesOrders
     * @return string[]
     */
    private function getEmailNotificationChoices(array $salesOrders)
    {
        $choices = [];
        foreach ( $salesOrders as $order ) {
            $choices[ $order->getEmail() ] = $order->getId();
        }
        return $choices;
    }
}
