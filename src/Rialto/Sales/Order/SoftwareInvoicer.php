<?php


namespace Rialto\Sales\Order;


use Exception;
use Psr\Log\LoggerInterface;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Invoice\SalesInvoice;
use Rialto\Sales\Invoice\SalesInvoiceProcessor;
use Rialto\Sales\SalesEvents;
use Rialto\Shipping\Export\DeniedPartyScreener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Automatically Invoice SalesOrders that contain only software items after
 * they are authorized.
 */
class SoftwareInvoicer implements EventSubscriberInterface
{
    /** @var DbManager */
    private $dbm;

    /** @var SalesOrderPaymentProcessor */
    private $paymentProcessor;

    /** @var SalesInvoiceProcessor */
    private $invoiceProcessor;

    /** @var DeniedPartyScreener */
    private $dps;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SalesEvents::ORDER_AUTHORIZED => 'handleOrderAuthorized',
        ];
    }

    public function __construct(DbManager $dbm,
                                SalesOrderPaymentProcessor $paymentProcessor,
                                SalesInvoiceProcessor $invoiceProcessor,
                                DeniedPartyScreener $dps,
                                LoggerInterface $logger)
    {
        $this->dbm = $dbm;
        $this->paymentProcessor = $paymentProcessor;
        $this->invoiceProcessor = $invoiceProcessor;
        $this->dps = $dps;
        $this->logger = $logger;
    }

    public function handleOrderAuthorized(SalesOrderEvent $event)
    {
        $order = $event->getOrder();
        if ($this->isIgnorable($order)) return;

        $invoice = new SalesInvoice($order);
        $invoice->setDeniedPartyScreener($this->dps);

        try {
            $this->processPayment($invoice);
            $this->processInvoice($invoice);
        } catch (Exception $ex) {
            $this->logger->error($ex);
        }
    }

    private function isIgnorable(SalesOrder $order): bool
    {
        return $order->isQuotation() || $this->orderContainsNonSoftware($order);
    }

    private function orderContainsNonSoftware(SalesOrder $order): bool
    {
        foreach ($order->getLineItems() as $lineItem) {
            if (!$lineItem->getStockItem()->getCategory()->isSoftware()) {
                return true;
            }
        }

        return false;
    }


    /**
     * @throws Exception
     */
    private function processPayment(SalesInvoice $invoice)
    {
        $this->dbm->beginTransaction();
        try {
            $this->paymentProcessor->processPayment($invoice);
            $this->dbm->flushAndCommit();
        } catch (Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
    }

    /**
     * @throws Exception
     */
    private function processInvoice(SalesInvoice $invoice): DebtorTransaction
    {
        $this->dbm->beginTransaction();
        try {
            $debtorTrans = $this->invoiceProcessor->processInvoice($invoice);
            $this->dbm->persist($debtorTrans);
            $this->dbm->flushAndCommit();
        } catch (Exception $ex) {
            $this->dbm->rollBack();
            $this->logger->error('Invoice processing aborted.');
            throw $ex;
        }
        return $debtorTrans;
    }
}