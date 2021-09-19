<?php

namespace Rialto\Purchasing\Quotation\Email;

use Gumstix\Storage\FileStorage;
use Rialto\Company\Company;
use Rialto\Email\Attachment\AttachmentZipper;
use Rialto\Email\Email;
use Rialto\Manufacturing\BuildFiles\BuildFiles;
use Rialto\Manufacturing\BuildFiles\PcbBuildFiles;
use Rialto\Purchasing\Order\Attachment\BuildFileSelector;
use Rialto\Purchasing\Quotation\QuotationRequest;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An emailed Request for Quotation (RFQ) sent to suppliers.
 */
class RequestForQuote extends Email
{
    /** @var QuotationRequest */
    private $rfq;

    /**
     * @var BuildFileSelector[]
     * @Assert\Valid(traverse=true)
     */
    private $attachments = [];

    public function __construct(QuotationRequest $rfq, Company $company)
    {
        $this->rfq = $rfq;
        $this->setFrom($rfq->getRequestedBy());
        if ($rfq->isTurboGeppetto()) {
            $this->subject = sprintf('[RFQ %s] Request for Panelized Board (multiple designs on a single panel) quotation from %s',
                $rfq->getId(), $company->getName());
        } else {
            $this->subject = sprintf('[RFQ %s] Request for quotation from %s',
                $rfq->getId(), $company->getName());
        }
        $this->template = 'purchasing.request_for_quote';
        $this->params = [
            'rfq' => $rfq,
        ];
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function getContentType()
    {
        return 'text/plain';
    }

    public function getAttachments()
    {
        return $this->attachments;
    }

    public function getSupplier()
    {
        return $this->rfq->getSupplier();
    }

    public function loadAttachments(FileStorage $storage)
    {
        foreach ($this->rfq->getItems() as $rItem) {
            $fullSku = $rItem->getFullSku();
            if (isset($this->attachments[$fullSku])) {
                continue;
            }
            $att = new BuildFileSelector();
            $buildFiles = BuildFiles::create(
                $rItem->getStockItem(),
                $rItem->getVersion(),
                $storage);
            $exclude = [];
            foreach (PcbBuildFiles::getInternalFilenames() as $filename) {
                $exclude[$filename] = true;
            }
            $att->attachBuildFiles($buildFiles, $exclude);
            $this->attachments[$fullSku] = $att;
        }
    }

    public function consolidateAttachments(AttachmentZipper $zipper)
    {
        $zipped = $zipper->consolidateAttachments($this->attachments);
        foreach ($zipped->getSelectedAttachments() as $attachment) {
            $this->addAttachment($attachment->createSwiftAttachment());
        }
    }

    public function setSent()
    {
        $this->rfq->setSent();
    }
}
