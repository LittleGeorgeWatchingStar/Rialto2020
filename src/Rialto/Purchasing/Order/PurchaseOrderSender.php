<?php

namespace Rialto\Purchasing\Order;

use Gumstix\Storage\FileStorage;
use Ramsey\Uuid\Uuid;
use Rialto\Cms\CmsEngine;
use Rialto\Email\Attachment\AttachmentZipper;
use Rialto\Email\MailerInterface;
use Rialto\Purchasing\Order\Attachment\PurchaseOrderAttachmentGenerator;
use Rialto\Purchasing\Order\Email\PurchaseOrderEmail;
use Rialto\Security\User\CurrentUserTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Emails POs to the supplier, or simply marks them as sent.
 */
class PurchaseOrderSender
{
    use CurrentUserTrait;

    /** @var PurchaseOrderAttachmentGenerator */
    private $attachmentGenerator;

    /** @var AttachmentZipper */
    private $attachmentZipper;

    /** @var OrderPdfGenerator */
    private $pdfGenerator;

    /** @var EngineInterface */
    private $templating;

    /** @var MailerInterface */
    private $mailer;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var CmsEngine */
    private $cmsEngine;

    /** @var FileStorage */
    private $storage;

    public function __construct(PurchaseOrderAttachmentGenerator $attachmentGenerator,
                                AttachmentZipper $attachmentZipper,
                                OrderPdfGenerator $pdfGenerator,
                                EngineInterface $templating,
                                MailerInterface $mailer,
                                EventDispatcherInterface $dispatcher,
                                CmsEngine $cmsEngine,
                                FileStorage $storage)
    {
        $this->attachmentGenerator = $attachmentGenerator;
        $this->attachmentZipper = $attachmentZipper;
        $this->pdfGenerator = $pdfGenerator;
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->dispatcher = $dispatcher;
        $this->cmsEngine = $cmsEngine;
        $this->storage = $storage;
    }

    public function createEmail(PurchaseOrder $order)
    {
        $sender = $this->getCurrentUser();
        $email = new PurchaseOrderEmail($order, $sender);
        $email->loadAdditionalAttachments($this->attachmentGenerator);
        $body = $this->cmsEngine->render('purchasing.purchase_order_email_body', [
            'order' => $order,
            'from' => $sender,
        ]);
        $email->setBody($body);
        return $email;
    }

    public function sendEmail(PurchaseOrderEmail $email, PurchaseOrder $order)
    {
        $uuid = Uuid::uuid4();
        $pdfData = $this->pdfGenerator->generatePdf($order);
        $filename = str_replace(" ", "_", "$order emailed $uuid.pdf");
        $this->storage->put($filename, $pdfData);
        $email->attachPdf($this->pdfGenerator->generatePdf($order));
        $email->consolidateAttachments($this->attachmentZipper);
        $this->mailer->send($email);
        $order->setSent($this->getCurrentUser(), 'emailed', $filename);
    }

    public function markAsSent(PurchaseOrder $order)
    {
        $uuid = Uuid::uuid4();
        $pdfData = $this->pdfGenerator->generatePdf($order);
        $filename = str_replace(" ", "_", "$order marked as sent $uuid.pdf");
        $this->storage->put($filename, $pdfData);
        $order->setSent($this->getCurrentUser(), 'marked as sent', $filename);
    }
}
