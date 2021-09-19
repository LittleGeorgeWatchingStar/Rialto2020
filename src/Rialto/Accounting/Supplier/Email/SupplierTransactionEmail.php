<?php

namespace Rialto\Accounting\Supplier\Email;

use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Email\Email;
use Rialto\Purchasing\Supplier\Contact\SupplierContact;

/**
 * Email sent to a supplier regarding a supplier transaction.
 */
class SupplierTransactionEmail extends Email
{
    /** @var SupplierTransaction */
    private $trans;

    public function __construct(SupplierTransaction $trans)
    {
        $this->trans = $trans;
    }

    /** @return SupplierContact[] */
    public function getContacts()
    {
        return $this->trans->getSupplier()->getActiveContacts();
    }

    /** @return string|null */
    public function getChequeNo()
    {
        foreach ( $this->trans->getBankTransactions() as $bankTrans) {
            if ( $bankTrans->isCheque() ) {
                return $bankTrans->getChequeNumber();
            }
        }
        return null;
    }

    public function getContentType()
    {
        return 'text/plain';
    }

}
