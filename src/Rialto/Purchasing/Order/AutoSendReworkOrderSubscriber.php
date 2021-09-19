<?php

namespace Rialto\Purchasing\Order;

use Rialto\Cms\CmsEngine;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\Mailable\Mailable;
use Rialto\Email\MailerInterface;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\Attachment\PurchaseOrderAttachmentGenerator;
use Rialto\Purchasing\Order\Email\PurchaseOrderEmail;
use Rialto\Sales\Returns\SalesReturnEvent;
use Rialto\Sales\SalesEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Listens for sales return test results and automatically sends the rework PO
 * to the manufacturer.
 */
class AutoSendReworkOrderSubscriber implements EventSubscriberInterface
{
    /** @var OrderPdfGenerator */
    private $generator;

    /** @var PurchaseOrderAttachmentGenerator */
    private $attachments;

    /** @var EngineInterface */
    private $templating;

    /** @var MailerInterface */
    private $mailer;

    /** @var CmsEngine */
    private $cmsEngine;

    public static function getSubscribedEvents()
    {
        return [
            SalesEvents::RETURN_DISPOSITION => 'onSalesReturnDisposition',
        ];
    }

    public function __construct(
        OrderPdfGenerator $generator,
        PurchaseOrderAttachmentGenerator $attachments,
        EngineInterface $templating,
        MailerInterface $mailer,
        CmsEngine $cmsEngine)
    {
        $this->generator = $generator;
        $this->attachments = $attachments;
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->cmsEngine = $cmsEngine;
    }

    public function onSalesReturnDisposition(SalesReturnEvent $event)
    {
        $rma = $event->getSalesReturn();
        foreach ( $rma->getLineItems() as $rmaItem ) {
            if ( $rmaItem->hasReworkOrder() ) {
                $this->sendPOIfNeeded($rmaItem->getReworkOrder());
            }
        }
    }

    private function sendPOIfNeeded(WorkOrder $wo)
    {
        assertion(true === $wo->isRework());
        $po = $wo->getPurchaseOrder();
        if (! $po->hasSupplier()) {
            return;
        }
        if ( $po->isSent() ) {
            return;
        }
        $sender = EmailPersonality::BobErbauer();
        $this->sendPO($po, $sender);
        $po->setSent($sender, 'auto-sent rework order');
    }

    private function sendPO(PurchaseOrder $po, Mailable $sender)
    {
        $email = new PurchaseOrderEmail($po, $sender);
        $this->addRecipients($po, $email);
        $email->attachPdf($this->generator->generatePdf($po));
        $email->loadAdditionalAttachments($this->attachments);
        $body = $this->cmsEngine->render('purchasing.purchase_order_email_body', [
            'order' => $po,
            'from' => $sender,
        ]);
        $email->setBody($body);
        $this->mailer->send($email);
    }

    private function addRecipients(PurchaseOrder $po, PurchaseOrderEmail $email)
    {
        $recipients = $po->getOrderContacts();
        $email->setTo($recipients);
        $email->addCc($po->getOwner());
    }

}
