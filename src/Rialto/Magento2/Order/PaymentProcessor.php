<?php

namespace Rialto\Magento2\Order;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Magento2\Api\Rest\InvoiceApi;
use Rialto\Magento2\Api\Rest\RestApiFactory;
use Rialto\Sales\Order\CapturePaymentEvent;
use Rialto\Sales\SalesEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handles payment events for Magento sales orders.
 */
class PaymentProcessor extends OrderListener implements EventSubscriberInterface
{
    /**
     * @var RestApiFactory
     */
    private $apiFactory;

    public function __construct(ObjectManager $om, RestApiFactory $apiFactory)
    {
        parent::__construct($om);
        $this->apiFactory = $apiFactory;
    }

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
            SalesEvents::CAPTURE_PAYMENT => 'createInvoiceAndCapturePayment',
        ];
    }

    /**
     * If the order is a Magento 2 order, create an invoice in Magento 2 and
     * capture the payment.
     */
    public function createInvoiceAndCapturePayment(CapturePaymentEvent $event)
    {
        $order = $event->getOrder();
        $store = $this->getStorefront($order);
        if (!$store) {
            return;
        }
        $invoice = $event->getInvoice();

        $api = $this->apiFactory->createInvoiceApi($store);
        $invoiceId = $api->createInvoice($invoice);
        //$this->capturePayment($api, $event, $invoiceId);
    }

    /**
     * Call this method if we decide to have Magento capture the payment
     * instead of Rialto.
     */
    private function capturePayment(InvoiceApi $api, CapturePaymentEvent $event, int $invoiceId)
    {
        $invoice = $event->getInvoice();
        $order = $invoice->getSalesOrder();
        $api->captureInvoice($invoiceId);

        $cardTransactions = $order->getCardTransactions();
        $authTransaction = end($cardTransactions);
        $authTransaction->capture($invoice->getAmountToCapture());
        $event->setChargeTransaction($authTransaction);
        $event->stopPropagation();
    }
}
