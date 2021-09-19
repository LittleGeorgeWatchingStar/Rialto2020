<?php

namespace Rialto\Purchasing\Order\Email;

use Rialto\Email\Attachment\AttachmentZipper;
use Rialto\Email\Email;
use Rialto\Email\Mailable\Mailable;
use Rialto\Purchasing\Order\Attachment\BuildFileSelector;
use Rialto\Purchasing\Order\Attachment\PurchaseOrderAttachmentGenerator;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Supplier\Contact\SupplierContact;
use Swift_Attachment;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An email sent to a supplier to notify them of a purchase order.
 */
class PurchaseOrderEmail extends Email
{
    /**
     * @var PurchaseOrder
     * @Assert\Valid
     */
    private $po;

    /**
     * Manages the additional attachments to the PO.
     *
     * @var BuildFileSelector[]
     * @Assert\Valid(traverse=true)
     */
    private $attachments = [];


    public function __construct(PurchaseOrder $po, Mailable $sender)
    {
        $this->po = $po;
        $this->subject = 'Purchase Order Number ' . $po->getId();
        $this->setFrom($sender);
        $this->setReplyTo($sender);
    }

    public function getSupplierContacts()
    {
        return $this->filterContacts($this->po->getSupplierContacts());
    }

    public function getOrderContacts()
    {
        return $this->filterContacts($this->po->getOrderContacts());
    }

    /**
     * Remove contacts that don't have an email address.
     */
    private function filterContacts(array $contacts): array
    {
        return array_filter($contacts, function (SupplierContact $c) {
            return $c->getEmail() != '';
        });
    }

    /**
     * Attaches the main purchase order PDF.
     *
     * This attachment is required and therefore does not go through
     * the AttachmentSelector.
     *
     * @param string $pdfData
     */
    public function attachPdf($pdfData)
    {
        $filename = sprintf('PO_%s.pdf', $this->po->getId());
        $attachment = new Swift_Attachment($pdfData, $filename, 'application/pdf');
        $this->addAttachment($attachment);
    }

    public function loadAdditionalAttachments(
        PurchaseOrderAttachmentGenerator $generator)
    {
        $this->attachments = $generator->gatherAttachments($this->po);
    }

    public function getAdditionalAttachments()
    {
        return $this->attachments;
    }

    public function isMissingAttachments()
    {
        foreach ($this->attachments as $selector) {
            if ($selector->isMissingAttachments()) {
                return true;
            }
        }
        return false;
    }

    /**
     * This is where we actually attach the selected files to the email.
     */
    public function consolidateAttachments(AttachmentZipper $zipper)
    {
        $consolidated = $zipper->consolidateAttachments($this->attachments);
        foreach ($consolidated->getSelectedAttachments() as $attachment) {
            $this->addAttachment($attachment->createSwiftAttachment());
        }
    }
}
