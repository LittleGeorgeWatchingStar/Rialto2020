<?php

namespace Rialto\Manufacturing\WorkOrder\Web;


use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Web\Serializer\ListableFacade;

class WorkOrderSummary
{
    use ListableFacade;

    /** @var WorkOrder */
    private $wo;

    public function __construct(WorkOrder $wo)
    {
        $this->wo = $wo;
    }

    public function getId()
    {
        return $this->wo->getId();
    }

    public function getPurchaseOrder()
    {
        return $this->wo->getPurchaseOrderNumber();
    }

    public function getLocation()
    {
        return $this->wo->getLocation()->getId();
    }

    public function getLocationName()
    {
        return $this->wo->getLocation()->getName();
    }

    public function getSku()
    {
        return $this->wo->getSku();
    }

    /**
     * @deprecated use getSku() instead
     */
    public function getStockCode()
    {
        return $this->getSku();
    }

    public function getVersion()
    {
        return (string) $this->wo->getVersion();
    }

    public function getCustomization()
    {
        $c = $this->wo->getCustomization();
        return $c ? $c->getId() : null;
    }

    public function getFullSku()
    {
        return $this->wo->getFullSku();
    }

    public function getDateCreated()
    {
        return $this->wo->getDateCreated();
    }

    public function getDateUpdated()
    {
        return $this->wo->getDateUpdated();
    }

    public function getDateClosed()
    {
        return $this->wo->getDateClosed();
    }

    public function getQtyOrdered()
    {
        return $this->wo->getQtyOrdered();
    }

    public function getQtyReceived()
    {
        return $this->wo->getQtyReceived();
    }

    public function getRework(){
        return $this->wo->isRework();
    }

}
