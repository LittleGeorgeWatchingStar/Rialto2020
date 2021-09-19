<?php

namespace Rialto\Sales\Order;

use Psr\Log\LoggerInterface;
use Rialto\Accounting\Card\CapturableInvoice;
use Rialto\Accounting\Card\CardTransaction;
use Rialto\Accounting\Debtor\DebtorCredit;
use Rialto\Accounting\Debtor\DebtorTransactionFactory;
use Rialto\Database\Orm\DbManager;
use Rialto\Payment\CardAuth;
use Rialto\Payment\PaymentGateway;
use Rialto\Sales\SalesEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Processes payments for a sales order.
 */
class SalesOrderPaymentProcessor
{
    /** @var DbManager */
    private $dbm;

    /** @var PaymentGateway */
    private $gateway;

    /** @var DebtorTransactionFactory */
    private $factory;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        DbManager $dbm,
        PaymentGateway $gateway,
        DebtorTransactionFactory $factory,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger)
    {
        $this->dbm = $dbm;
        $this->gateway = $gateway;
        $this->factory = $factory;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * Processes the payment for this invoice, if there is processing that
     * needs to be done.
     *
     * @return DebtorCredit|null
     *  The receipt, or null if no payment was processed.
     */
    public function processPayment(CapturableInvoice $invoice)
    {
        if ($invoice->getAmountToCapture() <= 0) {
            return null;
        }
        $event = $this->capturePayment($invoice);
        $this->logWarnings($event->getWarnings());
        $cardTrans = $event->getChargeTransaction();
        if (!$cardTrans) {
            $balance = $invoice->getAmountToCapture();
            $msg = 'No payment processor found to capture balance of $%s on %s';
            throw new \UnexpectedValueException(sprintf($msg,
                number_format($balance, 2),
                $invoice));
        }
        $order = $invoice->getSalesOrder();
        assertion(null != $cardTrans->getSalesOrder());
        assertion($order === $cardTrans->getSalesOrder());

        $this->dbm->persist($cardTrans);
        /* After this point, the customer's card HAS BEEN CHARGED. */
        $receipt = $this->factory->createCardReceipt($cardTrans);
        $this->notifyOfCharge($order);

        $this->logger->notice(sprintf('Charged credit card for $%s.',
            number_format($cardTrans->getAmountCaptured(), 2)
        ));
        return $receipt;
    }

    /**
     * We capture the payment by dispatching an event that various storefronts
     * can listen for and respond to. Each storefront has a different means
     * of doing so, which is why this level of indirection is required.
     *
     * @return CapturePaymentEvent
     */
    private function capturePayment(CapturableInvoice $invoice)
    {
        $event = new CapturePaymentEvent($invoice);
        $this->dispatcher->dispatch(SalesEvents::CAPTURE_PAYMENT, $event);
        return $event;
    }

    private function logWarnings(array $warnings)
    {
        foreach ($warnings as $w) {
            $this->logger->warning($w);
        }
    }

    private function notifyOfCharge(SalesOrder $order)
    {
        $event = new SalesOrderEvent($order);
        $this->dispatcher->dispatch(SalesEvents::ORDER_CHARGED, $event);
    }

    /**
     * Authorizes (but does not charge) the credit card for the order.
     */
    public function authorizeCard(CardAuth $cardInfo,
                                  SalesOrder $order): CardTransaction
    {
        $cardTrans = $this->gateway->authorize($cardInfo, $order);
        assertion($cardTrans->canBeCaptured());
        $order->addCardTransaction($cardTrans);
        $card = $cardTrans->getCreditCard();

        $this->logger->notice(sprintf('Authorized %s in the amount of $%s for %s.',
            $card,
            number_format($cardTrans->getAmountAuthorized(), 2),
            $order));

        return $cardTrans;
    }
}
