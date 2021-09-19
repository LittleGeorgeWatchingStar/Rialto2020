<?php

namespace Rialto\Manufacturing\Task\Web;

use Rialto\Company\Company;
use Rialto\Email\Email;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Purchasing\Order\PurchaseOrder;

/**
 * Email sent to a manufacturer to remind them that there are actionable
 * tasks for them to complete.
 */
class ProductionTaskReminderEmail extends Email
{
    /**
     * @param PurchaseOrder[] $orders
     */
    public function __construct(Company $company, array $orders)
    {
        $this->template = 'manufacturing/production/task/email.html.twig';
        $this->params = [
            'orders' => $orders,
        ];
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->subject = sprintf(
            'The following orders for %s need your attention',
            $company->getShortName());
    }
}
