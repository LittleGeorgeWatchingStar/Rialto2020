<?php

namespace Rialto\Manufacturing\Kit\Email;

use Rialto\Company\Company;
use Rialto\Email\Email;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Manufacturing\Kit\WorkOrderKit;
use Rialto\Purchasing\Supplier\Contact\SupplierContact;
use Rialto\Stock\Facility\Facility;

/**
 * Email sent to the manufacturer when a work order kit is sent.
 */
class WorkOrderKitEmail extends Email
{
    public function __construct(WorkOrderKit $kit, Company $company)
    {
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->subject = sprintf('%s has just prepared a transfer', $company->getShortName());

        $transfer = $kit->getTransfer();

        $this->template = 'manufacturing/work-order/kit-email.html.twig';
        $this->params = [
            'transfer' => $transfer,
            'company' => $company,
        ];
        $this->attachCsv($kit, $company);
        $this->setRecipients($transfer->getDestination());
    }

    private function attachCsv(WorkOrderKit $kit, Company $company)
    {
        $transfer = $kit->getTransfer();
        $csv = KitCsv::create($kit);
        $filename = sprintf("%s_transfer_%s.csv",
            $company->getShortName(),
            $transfer->getId());
        $this->addAttachmentFromFileData($csv->toString(), 'text/csv', $filename);
    }

    /**
     * @param SupplierContact[] $recipients
     */
    private function setRecipients(Facility $destination)
    {
        $supplier = $destination->getSupplier();
        foreach ( $supplier->getKitContacts() as $recipient ) {
            if (! $recipient->getEmail() ) continue;
            $this->addTo($recipient);
        }
    }
}
