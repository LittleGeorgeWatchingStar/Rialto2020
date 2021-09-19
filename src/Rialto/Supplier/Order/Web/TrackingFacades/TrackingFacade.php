<?php

namespace Rialto\Supplier\Order\Web\TrackingFacades;

use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Ups\TrackingRecord\TrackingRecord;
use Twig\Environment;

class TrackingFacade
{
    /** @var TrackingRecord|null */
    private $trackingRecord;

    /** @var PurchaseOrder */
    private $purchaseOrder;

    /** @var Environment */
    private $twig;

    /** @var PurchaseOrderFacade */
    private $purchaseOrdersFacades;


    public function __construct(?TrackingRecord $trackingRecord, PurchaseOrder $purchaseOrder, Environment $twig)
    {
        $this->trackingRecord = $trackingRecord;
        $this->purchaseOrder = $purchaseOrder;
        $this->twig = $twig;
        $this->purchaseOrdersFacades = [];
    }

    public function getPurchaseOrder()
    {
        return new PurchaseOrderFacade($this->purchaseOrder, $this->twig);
    }

    public function getTrackingNumber()
    {
        return $this->trackingRecord ? $this->trackingRecord->getTrackingNumber(): '';
    }

    public function getDateCreated()
    {
        $dateCreated = $this->trackingRecord ? $this->trackingRecord->getDateCreated() : null;
        return $dateCreated ? $dateCreated->format('Y-m-d') : '';
    }

    public function getDateDelivered()
    {
        $dateDelivered = $this->trackingRecord ? $this->trackingRecord->getDateDelivered() : null;
        return $dateDelivered ? $dateDelivered->format('Y-m-d') : '';
    }

    public function getDateUpdated()
    {
        $dateUpdate = $this->trackingRecord ? $this->trackingRecord->getDateUpdated() : null;
        return $dateUpdate ? $dateUpdate->format('Y-m-d') : '';
    }

    public function getTrackingStatus()
    {
        if ($this->trackingRecord) {
            $template = $this->twig->createTemplate('{{ trackingNumber | track_icon }}');
            return $template->render([
                'trackingNumber' => $this->trackingRecord->getTrackingNumber(),
            ]);
        } else {
            return '';
        }
    }
}