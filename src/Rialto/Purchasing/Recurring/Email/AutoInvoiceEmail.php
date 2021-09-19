<?php

namespace Rialto\Purchasing\Recurring\Email;

use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Purchasing\Recurring\Cli\AutoInvoiceCommand;
use Rialto\Purchasing\Recurring\RecurringInvoice;

/**
 * Notifies subscribers of the results of the nightly auto-invoice entry.
 *
 * @see AutoInvoiceCommand
 */
class AutoInvoiceEmail extends SubscriptionEmail
{
    const TOPIC = 'purchasing.auto_invoice';

    /** @var SupplierTransaction[] */
    private $newInvoices = [];

    /** @var SupplierTransaction[] */
    private $alreadyEntered = [];

    /** @var RecurringInvoice[] */
    private $invalid = [];

    private $overdue = [];

    protected function getSubscriptionTopic()
    {
        return self::TOPIC;
    }

    public function addNewInvoice(SupplierTransaction $newInvoice)
    {
        $this->newInvoices[] = $newInvoice;
    }

    public function addAlreadyEntered(SupplierTransaction $invoice)
    {
        $this->alreadyEntered[] = $invoice;
    }

    public function addInvalid(RecurringInvoice $invalid)
    {
        $this->invalid[] = $invalid;
    }

    public function setOverdueInvoices(array $overdue)
    {
        $this->overdue = $overdue;
    }

    public function prepare()
    {
        $this->subject = 'AutoInvoice';
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->template = "purchasing/invoice/auto/email.html.twig";
        $this->params = [
            'newInvoices' => $this->newInvoices,
            'alreadyEntered' => $this->alreadyEntered,
            'overdue' => $this->overdue,
            'invalid' => $this->invalid,
        ];
    }

}
