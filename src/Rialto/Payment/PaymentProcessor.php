<?php

namespace Rialto\Payment;

use Psr\Log\LoggerInterface;
use Rialto\Sales\Order\CapturePaymentEvent;
use Rialto\Sales\Order\SalesOrderEvent;
use Rialto\Sales\SalesEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Captures credit card authorizations for managed sales orders.
 *
 * Each storefront has a class like this that captures payments for that
 * storefront. This class captures payments for managed sales that do not
 * come through a particular storefront.
 */
class PaymentProcessor implements EventSubscriberInterface
{
    /** @var PaymentGateway */
    private $gateway;

    /** @var LoggerInterface */
    private $logger;

    /**
     * The requests to capture or void a credit card authorization should be
     * done last in Rialto. This gives the storefronts a chance to process
     * the payment first.
     */
    const PRIORITY = -20;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     */
    public static function getSubscribedEvents()
    {
        return [
            SalesEvents::CAPTURE_PAYMENT => ['capturePayment', self::PRIORITY],
            SalesEvents::ORDER_CLOSED => ['voidUncapturedTransactions', self::PRIORITY],
        ];
    }

    public function __construct(PaymentGateway $gateway, LoggerInterface $logger)
    {
        $this->gateway = $gateway;
        $this->logger = $logger;
    }

    /**
     * Checks to see if the order is a managed sale, and if so,
     * charges the card and records the charge.
     */
    public function capturePayment(CapturePaymentEvent $event)
    {
        $order = $event->getOrder();
        assertion(!$order->isFullyPaid());

        /* If there is no credit card auth, then this is not a credit card
         * transaction. */
        $authTrans = $order->getCardAuthorization();
        if (!$authTrans) {
            return;
        }
        $invoice = $event->getInvoice();
        $chargeTrans = $this->gateway->chargeCard(
            $authTrans,
            $invoice->getAmountToCapture(),
            $order->getOrderNumber()
        );

        $event->setChargeTransaction($chargeTrans);
        $event->stopPropagation();
    }

    /**
     * Void any credit card authorizations that have not been captured.
     *
     * Called when the order is closed.
     */
    public function voidUncapturedTransactions(SalesOrderEvent $event)
    {
        $order = $event->getOrder();
        assertion($order->isCompleted());

        $authTrans = $order->getCardAuthorization();
        if (!$authTrans) {
            return;
        }
        if ($authTrans->isVoid()) {
            return;
        }

        $invoiceNumber = $order->getCustomerReference();
        try {
            $this->gateway->void($authTrans, $invoiceNumber);
            $this->logger->notice("Voided $authTrans.");
        } catch (GatewayException $ex) {
            if ($ex->isTransactionNotFound()) {
                $this->logger->warning("$authTrans not found. It might have expired.");
            } else {
                throw $ex;
            }
        }
    }
}
