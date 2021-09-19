<?php

namespace Rialto\Purchasing\Receiving\Notify;


use Rialto\Email\Email;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Purchasing\Receiving\GoodsReceivedNotice;
use Rialto\Security\User\User;

class OrderReceivedEmail extends Email
{
    public function __construct(User $owner, GoodsReceivedNotice $grn)
    {
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->addTo($owner);
        $this->setSubject($this->makeSubject($grn));
        $this->setTemplate('purchasing/receiving/email/order-received.html.twig');

        if ($grn->getPurchaseOrder()) {
            $receiveFacilityFromGRN = $grn->getReceivedInto();
            $name = $receiveFacilityFromGRN->getName();
        } else {
            $name = null;
        }

        $this->params = [
            'grn' => $grn,
            'order' => $grn->getPurchaseOrder(),
            'receivedFacilityName' => $name,
        ];
    }

    private function makeSubject(GoodsReceivedNotice $grn)
    {
        $receivedBy = $grn->getReceiver();
        $order = $grn->getPurchaseOrder();
        $complete = $order->isCompleted();

        return sprintf('%s received %s%s',
            $receivedBy,
            $order,
            $complete ? '' : ' (INCOMPLETE)');
    }
}
