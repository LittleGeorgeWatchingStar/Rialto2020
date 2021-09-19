<?php

namespace Rialto\Stock\Count\Email;

use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Security\User\User;
use Rialto\Stock\Count\StockCount;
use Rialto\Stock\Facility\Facility;

/**
 * This email is sent to the location manager when the admin requests
 * a stock count.
 */
class StockCountRequestEmail extends SubscriptionEmail
{
    public function __construct(StockCount $count)
    {
        $requestedBy = $count->getRequestedBy();
        $this->setFrom($requestedBy);
        $this->subject = 'Stock count requested';
        $this->template = "stock/count/request-email.html.twig";
        $this->setTo($this->getRecipients($count->getLocation()));
        $this->params = [
            'count' => $count,
        ];
    }

    private function getRecipients(Facility $location)
    {
        $supplier = $location->getSupplier();
        return $supplier ? $supplier->getKitContacts() :
            [$location->getContact()];
    }

    protected function getSubscriptionTopic()
    {
        return 'stock.stock_count';
    }

    /**
     * Subscribers get CCed; the location manager is the main
     * recipient.
     */
    protected function addSubscriber(User $user)
    {
        $this->addCc($user);
    }

}
