<?php

namespace Rialto\Sales\Order\Email;

use Rialto\Company\Company;
use Rialto\Email\Email;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\SalesPdfGenerator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * An email sent to a customer regarding a sales order.
 */
class SalesOrderEmail extends Email
{
    /**
     * These templates refer to CMS entries that contain the email body.
     */
    const BUY_ONLINE = 'sales.email.buy_online';
    const PAY_BALANCE = 'sales.email.pay_balance';
    const IN_STOCK = 'sales.email.in_stock_pay_balance';
    const PAY_DEPOSIT = 'sales.email.pay_deposit';

    const ATTACH_INVOICE = 'pro-forma invoice';
    const ATTACH_QUOTE = 'quotation';

    /** @var Company */
    private $company = null;

    /** @var SalesOrder */
    private $order = null;

    private $attachmentType = null;

    public function __construct(Company $company, SalesOrder $order)
    {
        $this->company = $company;
        $this->setFrom($company);
        $this->order = $order;
        $this->addTo($order);
        $this->subject = sprintf('%s %s',
            $company->getShortName(),
            $order->getSummaryWithCustomerRef());
    }

    public function getContentType()
    {
        return 'text/plain';
    }

    /**
     * Returns the list of valid pre-defined email templates.
     * @return string[]
     */
    public static function getTemplates()
    {
        return [
            'Order is too small; buy online' => self::BUY_ONLINE,
            'Quotation/invoice is attached, pay the full amount' => self::PAY_BALANCE,
            'Order is in stock; pay the balance' => self::IN_STOCK,
            'Quotation/invoice is attached; pay the deposit' => self::PAY_DEPOSIT,
        ];
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public static function getAttachmentTypes()
    {
        $types = [self::ATTACH_INVOICE, self::ATTACH_QUOTE];
        return array_combine($types, $types);
    }

    public function getAttachmentType()
    {
        return $this->attachmentType;
    }

    public function setAttachmentType($type)
    {
        $this->attachmentType = trim($type);
    }

    /**
     * @Assert\Callback
     */
    public function validateAttachmentType(ExecutionContextInterface $context)
    {
        if ($this->template == self::BUY_ONLINE) {
            return;
        }

        if (! $this->attachmentType) {
            $context->buildViolation(
                "An attachment is required for the selected template.")
                ->atPath('attachmentType')
                ->addViolation();
        }
    }

    public function createAttachment(SalesPdfGenerator $generator)
    {
        if ($this->attachmentType) {
            $pdf = $generator->generatePdf($this->order, $this->attachmentType);
            $this->addAttachmentFromFileData($pdf, 'application/pdf', $this->getAttachmentFilename());
        }
    }

    public function getAttachmentFilename()
    {
        return self::getPdfFilename($this->company, $this->order, $this->attachmentType);
    }

    public static function getPdfFilename(Company $company, SalesOrder $order, $attachmentType)
    {
        return sprintf('%s %s %s.pdf',
            $company->getShortName(),
            $attachmentType,
            $order->getOrderNumber());
    }
}
