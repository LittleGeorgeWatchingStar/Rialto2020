<?php

namespace Rialto\Manufacturing\Bom\Bag;

use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Stock\Item\Version\ItemVersion;

/**
 * Sent when no bag can be found that fits a new board.
 */
class NoMatchingBagEmail extends SubscriptionEmail
{
    const TOPIC = 'manufacturing.no_matching_bag';

    public function __construct(ItemVersion $itemVersion, BagFinder $finder)
    {
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->template = 'manufacturing/bom/noMatchingBag.html.twig';
        $this->subject = "No bag found that fits ". $itemVersion->getFullSku();

        $this->params = [
            'itemVersion' => $itemVersion,
            'incomplete' => $finder->getBagsWithMissingDimensions(),
        ];
    }

    protected function getSubscriptionTopic()
    {
        return self::TOPIC;
    }

}
