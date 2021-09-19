<?php

namespace Rialto\Manufacturing\Email;

use Rialto\Email\EmailListener;
use Rialto\Manufacturing\Audit\AuditEvent;
use Rialto\Manufacturing\BuildFiles\BuildFilesEvent;
use Rialto\Manufacturing\BuildFiles\Email\BuildFilesUploadNotification;
use Rialto\Manufacturing\ManufacturingEvents;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\PurchasingEvents;
use Rialto\Purchasing\Receiving\GoodsReceivedEvent;
use SplObjectStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens for manufacturing events that require an email to be sent.
 */
class EmailEventSubscriber extends EmailListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ManufacturingEvents::PURCHASE_ORDER_SHORTAGE => 'notifyOfShortage',
            ManufacturingEvents::onBuildFilesUpload => 'onBuildFilesUpload',
            PurchasingEvents::GOODS_RECEIVED => 'notifyManufacturerOfWastage',
        ];
    }

    public function notifyOfShortage(AuditEvent $event)
    {
        if (! $event->isSendEmail()) {
            return;
        }
        $po = $event->getPurchaseOrder();
        $items = $event->getShortItems();
        $email = new ShortageEmail($po, $items, $this->getCurrentUser());
        $email->loadSubscribers($this->om);

        $this->send($email);
    }

    public function onBuildFilesUpload(BuildFilesEvent $event)
    {
        $buildFiles = $event->getBuildFiles();
        /** @var $repo PurchaseOrderRepository */
        $repo = $this->om->getRepository(PurchaseOrder::class);
        $orders = $repo->findOpenOrdersNeedingBuildFiles($buildFiles);

        $index = $this->indexOrdersByOwner($orders);

        $currentUser = $this->getCurrentUser();
        foreach ( $index as $owner ) {
            $poList = $index[$owner];
            $email = new BuildFilesUploadNotification($currentUser, $buildFiles, $owner, $poList);
            $this->send($email);
        }
    }

    /**
     * @param PurchaseOrder[] $orders
     * @return SplObjectStorage
     */
    private function indexOrdersByOwner(array $orders)
    {
        $index = new SplObjectStorage();
        $currentUser = $this->getCurrentUser();
        foreach ( $orders as $po ) {
            $owner = $po->getOwner();
            if (! $owner ) {
                continue;
            }
            if ( $currentUser->isEqualTo($owner) ) continue;

            $list = isset($index[$owner]) ? $index[$owner] : [];
            $list[] = $po;
            $index[$owner] = $list;
        }
        return $index;
    }

    /**
     * @param GoodsReceivedEvent $event
     */
    public function notifyManufacturerOfWastage(GoodsReceivedEvent $event)
    {
        $grn = $event->getGrn();
        $po = $grn->getPurchaseOrder();
        if (! $po->hasSupplier() ) {
            return;
        }
        $contacts = $po->getKitContacts();
        if ( count($contacts) == 0 ) {
            return;
        }
        $approvedBy = $this->getCurrentUser();
        foreach ( $grn->getItems() as $grnItem) {
            if (! $grnItem->isWorkOrder() ) {
                continue;
            }
            if ( $grnItem->isDiscarded() ) {
                $email = new WastageApproval($po, $grnItem, $approvedBy);
                $email->setTo($contacts);
                $email->addCc($approvedBy);
                $email->addCc($po->getOwner());
                $this->send($email);
            }
        }
    }
}
