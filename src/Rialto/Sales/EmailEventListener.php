<?php

namespace Rialto\Sales;

use Rialto\Accounting\Debtor\Credit\CustomerReceipt;
use Rialto\Accounting\Debtor\CustomerCreditEvent;
use Rialto\Company\Company;
use Rialto\Database\Orm\DbManager;
use Rialto\Email\MailerInterface;
use Rialto\Sales\Customer\Email\CustomerReceiptEmail;
use Rialto\Sales\Invoice\Email\SalesInvoiceAndShipmentEmail;
use Rialto\Sales\Invoice\SalesInvoiceEvent;
use Rialto\Sales\Order\OrderUpdateListener;
use Rialto\Sales\Returns\Disposition\SalesReturnDispositionEmail;
use Rialto\Sales\Returns\SalesReturnEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens for and responds to sales events which require that emails be sent.
 */
class EmailEventListener implements EventSubscriberInterface
{
    /**
     * Needs to be lower priority than OrderUpdateListener.
     *
     * @see OrderUpdateListener
     */
    const CREDIT_PRIORITY = -20;

    /**
     * Needs to be lower priority than listeners that, eg, add shipments
     * to the order.
     */
    const INVOICE_PRIORITY = -20;

    /** @var MailerInterface */
    private $mailer;

    /** @var DbManager */
    private $dbm;

    /** @var SalesPdfGenerator */
    private $pdfGenerator;

    public function __construct(MailerInterface $mailer,
                                DbManager $dbm,
                                SalesPdfGenerator $generator)
    {
        $this->mailer = $mailer;
        $this->dbm = $dbm;
        $this->pdfGenerator = $generator;
    }

    public static function getSubscribedEvents()
    {
        return [
            SalesEvents::CUSTOMER_CREDIT => ['notifyCustomerOfReceipt', self::CREDIT_PRIORITY],
            SalesEvents::ORDER_INVOICE => ['notifyCustomerOfShipment', self::INVOICE_PRIORITY],
            SalesEvents::RETURN_DISPOSITION => 'notifyAuthorizerOfDisposition',
        ];
    }

    public function notifyCustomerOfReceipt(CustomerCreditEvent $event)
    {
        if (!$event->isSendEmail()) {
            return;
        }

        $credit = $event->getCredit();
        if (!$credit instanceof CustomerReceipt) {
            return;
        }

        $company = $this->dbm->need(Company::class, Company::DEFAULT_ID);
        $email = new CustomerReceiptEmail($company, $credit, $event->isConfirmation());
        $this->mailer->send($email);
    }

    public function notifyCustomerOfShipment(SalesInvoiceEvent $event)
    {
        if (!$event->isEmailEnabled()) {
            return;
        }

        $invoice = $event->getInvoice();
        $company = $this->dbm->need(Company::class, Company::DEFAULT_ID);
        $email = new SalesInvoiceAndShipmentEmail(
            $invoice, $company, $event->getShipment()
        );
        $data = $this->pdfGenerator->generateDebtorTransactionPdf($invoice->getDebtorTransaction());
        $email->setPdfData($data);
        $this->mailer->send($email);
    }

    public function notifyAuthorizerOfDisposition(SalesReturnEvent $event)
    {
        $rma = $event->getSalesReturn();
        $email = new SalesReturnDispositionEmail($rma);
        $email->loadSubscribers($this->dbm);
        if (!$email->hasRecipients()) {
            return;
        }
        $this->mailer->send($email);
    }
}
