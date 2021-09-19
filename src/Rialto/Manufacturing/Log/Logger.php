<?php

namespace Rialto\Manufacturing\Log;

use Psr\Log\LoggerInterface;
use Rialto\Email\Mailable\Mailable;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Stock\Facility\Facility;

/**
 * Logs manufacturing events.
 */
class Logger
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs that a reminder was sent to the CMs to do their required tasks.
     *
     * @param Facility $loc
     * @param Mailable[] $recipients
     * @param PurchaseOrder[] $orders
     * @return string
     */
    public function productionTaskReminder(Facility $loc, array $recipients, array $orders)
    {
        $msg = sprintf("Task reminder sent to %s at %s about the following POs: %s.",
            $this->formatRecipients($recipients),
            $loc->getName(),
            $this->formatOrders($orders));

        $this->logger->notice($msg);
        return $msg;
    }

    /**
     * @param Mailable[] $recipients
     * @return string
     */
    private function formatRecipients(array $recipients)
    {
        $emails = array_map(function(Mailable $recipient) {
            return $recipient->getEmail();
        }, $recipients);
        return join(', ', $emails);
    }

    /**
     * @param PurchaseOrder[] $orders
     * @return string
     */
    private function formatOrders(array $orders)
    {
        $poNumbers = array_map(function(PurchaseOrder $order) {
            return $order->getId();
        }, $orders);
        return join(', ', $poNumbers);
    }

}
