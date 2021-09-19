<?php

namespace Rialto\Manufacturing\Email;

use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Manufacturing\Audit\AuditItem;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Security\User\User;

/**
 * Notifies the relevant parties that a work order was audited by the
 * manufacturer and found to be missing some parts.
 */
class ShortageEmail extends SubscriptionEmail
{
    const TOPIC = 'manufacturing.work_order_shortage';

    /** @var PurchaseOrder */
    private $po;

    /**
     * @param $short AuditItem[]
     */
    public function __construct(PurchaseOrder $po, array $shortages, User $auditedBy)
    {
        $this->po = $po;
        assertion(count($shortages) > 0);

        $this->subject = ucfirst("{$this->po} is not kit complete");
        $this->template = 'manufacturing/shortage/email.html.twig';
        $this->params = [
            'po' => $this->po,
            'shortages' => $shortages,
            'auditedBy' => $auditedBy,
        ];

        $this->setFrom(EmailPersonality::BobErbauer());
        $this->addTo($po->getOwner());
    }

    protected function getSubscriptionTopic()
    {
        return self::TOPIC;
    }
}
