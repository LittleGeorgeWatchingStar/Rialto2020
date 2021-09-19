<?php

namespace Rialto\Purchasing\Receiving;

use Psr\Log\LoggerInterface;
use Rialto\Purchasing\PurchasingEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Records GRNs in the system log.
 */
class GoodsReceivedLogger implements EventSubscriberInterface
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            PurchasingEvents::GOODS_RECEIVED => 'logGrn',
        ];
    }

    public function logGrn(GoodsReceivedEvent $event)
    {
        $grn = $event->getGrn();
        $po = $grn->getPurchaseOrder();
        $message = sprintf('%s has received %s',
            $grn->getReceiver(),
            $grn->getDescription());
        $context = [];
        if ( $po ) {
            $context['tags']['po'] = $po->getId();
        }
        $this->logger->notice($message, $context);
    }

}
