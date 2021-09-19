<?php

namespace Rialto\Purchasing\Order\Email;

use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Purchasing\Order\Cli\AutoOrderCommand;

/**
 * Notifies subscribers of the results of the nightly automated stock ordering.
 *
 * @see AutoOrderCommand
 */
class AutoOrderEmail extends SubscriptionEmail
{
    const TOPIC = 'purchasing.auto_order';

    protected function getSubscriptionTopic()
    {
        return self::TOPIC;
    }

    public function __construct(array $needs, array $errors)
    {
        $this->subject = 'Auto-order stock';
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->template = "purchasing/order/auto-email.html.twig";
        $this->params = [
            'needs' => $needs,
            'errors' => $errors,
            'dryrun' => false,
        ];
    }

    public function setDryRun($bool)
    {
        $this->params['dryrun'] = $bool;
    }

    public function shouldBeSent()
    {
        return $this->params['dryrun']
            || count($this->params['needs']) > 0
            || count($this->params['errors']) > 0;
    }
}
