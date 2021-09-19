<?php

namespace Rialto\Accounting\Debtor\Email;

use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Email\Email;

class DebtorTransactionEmail extends Email
{
    /** @var DebtorTransaction */
    protected $debtorTrans = null;

    public function __construct(DebtorTransaction $debtorTrans)
    {
        $this->debtorTrans = $debtorTrans;
        $this->subject = ucfirst(sprintf('%s for %s',
            $debtorTrans->getLabel(),
            $debtorTrans->getCustomer()));
        $this->addTo($this->debtorTrans->getCustomer());
        $this->template = "accounting/debtor/transaction/email.txt.twig";
        $this->params = [
            'debtorTrans' => $this->debtorTrans,
        ];
    }

    public function getContentType()
    {
        return 'text/plain';
    }

    public function setPdfData($data)
    {
        $filename = str_replace(" ", "", $this->subject) . ".pdf";
        $this->addAttachmentFromFileData($data, 'application/pdf', $filename);
    }
}

