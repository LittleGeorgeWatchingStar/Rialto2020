<?php

namespace Rialto\Manufacturing\Email;


use Rialto\Email\Email;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Receiving\GoodsReceivedItem;
use Rialto\Security\User\User;

class WastageApproval extends Email
{
    public function __construct(
        PurchaseOrder $po,
        GoodsReceivedItem $grnItem,
        User $approvedBy)
    {
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->setSubject(sprintf('Request to scrap %s approved', $po));
        $this->template = 'manufacturing/wastage-approval/email.html.twig';
        $this->params = [
            'po' => $po,
            'supplier' => $po->getSupplier(),
            'item' => $grnItem->getProducer()->getSku(),
            'qtyWasted' => $grnItem->getQtyReceived(),
            'approvedBy' => $approvedBy,
        ];
    }
}
