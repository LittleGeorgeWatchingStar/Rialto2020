<?php

namespace Rialto\Accounting\Web;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\Bank\Statement\BankStatement;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Web\TwigExtensionTrait;
use Twig\Extension\AbstractExtension;

/**
 * Twig extensions for the accounting bundle.
 */
class AccountingExtension extends AbstractExtension
{
    use TwigExtensionTrait;

    /** @var ObjectManager */
    private $om;

    /** @var AccountingRouter */
    private $router;

    public function __construct(
        ObjectManager $om,
        AccountingRouter $router)
    {
        $this->om = $om;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            $this->simpleFunction('accounting_event_link', 'eventLink', ['html']),
            $this->simpleFunction('transaction_link', 'transactionLink', ['html']),
            $this->simpleFunction('debtor_trans_link', 'debtorTransLink', ['html']),
            $this->simpleFunction('supplier_trans_link', 'supplierTransLink', ['html']),
            $this->simpleFunction('bank_statement_link', 'bankStatementLink', ['html']),
            $this->simpleFunction('bank_trans_link', 'bankTransLink', ['html']),
        ];
    }

    public function eventLink(AccountingEvent $event)
    {
        $sysType = $event->getSystemType();
        $typeNo = $event->getSystemTypeNumber();
        $label = $sysType->getName() .' '. $typeNo;
        $trans = $this->om->getRepository(Transaction::class)
            ->findOneBy(['systemType' => $sysType, 'groupNo' => $typeNo]);

        return $this->transactionLink($trans, $label);
    }

    public function transactionLink(Transaction $trans = null, $label = null)
    {
        if (!$trans) {
            return $this->none();
        }
        $label = $label ?: $trans->getId();
        $url = $this->router->transactionView($trans);
        return $this->link($url, $label);
    }

    public function debtorTransLink(DebtorTransaction $trans = null, $label = null)
    {
        if (!$trans) {
            return $this->none();
        }
        $label = $label ?: $trans->getLabel();
        $url = $this->router->debtorTransView($trans);
        return $this->link($url, $label);
    }

    public function supplierTransLink(SupplierTransaction $trans = null, $label = null)
    {
        if (!$trans) {
            return $this->none();
        }
        $label = $label ?: $trans->getLabel();
        $url = $this->router->supplierTransView($trans);
        return $this->link($url, $label);
    }

    public function bankStatementLink(BankStatement $statement = null, $label = null)
    {
        if (!$statement) {
            return $this->none();
        }
        $label = $label ?: $statement->getId();
        $url = $this->router->bankStatementView($statement);
        return $this->link($url, $label);
    }

    public function bankTransLink(BankTransaction $trans = null, $label = null)
    {
        if (!$trans) {
            return $this->none();
        }
        $label = $label ?: $trans->getId();
        $url = $this->router->bankTransactionView($trans);
        return $this->link($url, $label);
    }
}
