<?php

namespace Rialto\Sales\Customer\Email;

use Rialto\Accounting\Debtor\Credit\CustomerReceipt;
use Rialto\Company\Company;
use Rialto\Email\Email;

/**
 * This email is sent to confirm receipt of a payment from a customer.
 */
class CustomerReceiptEmail extends Email
{
    private $company;
    private $receipt;
    private $order;

    public function __construct(Company $company, CustomerReceipt $receipt, $isConfirmation)
    {
        $this->company = $company;
        $this->receipt = $receipt;
        $this->order = $receipt->getSalesOrder();
        $this->template = 'sales/customer/receipt-email.html.twig';
        $this->params = [
            'receipt' => $receipt,
            'order' => $this->order,
            'isConfirmation' => $isConfirmation,
        ];
        $this->subject = $this->determineSubject($isConfirmation);
        $this->setFrom($company);
        $this->addTo($receipt); // implements Mailable
    }

    private function determineSubject($isConfirmation)
    {
        $type = $this->receipt->getDescription();
        $company = $this->company->getShortName();
        if ( $this->order ) {
            $orderNo = $this->order->getOrderNumber();
            if ( $isConfirmation ) {
                return "$company quotation $orderNo has been confirmed into an order";
            }
            else {
                return "$company has received your $type payment for order $orderNo";
            }
        }
        else {
            return "$company has received your $type payment";
        }
    }

}
