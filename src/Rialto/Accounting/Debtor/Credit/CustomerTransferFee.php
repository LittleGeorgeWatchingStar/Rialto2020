<?php

namespace Rialto\Accounting\Debtor\Credit;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Sales\Order\SalesOrder;

class CustomerTransferFee extends CreditNote
{
    public function __construct(SalesOrder $order)
    {
        parent::__construct($order->getCustomer());
        $this->setSalesOrder($order);
        $this->setMemo("Bank transfer fee for $order");
        $this->setToAccount( GLAccount::fetchBankCharges() );
    }
}
