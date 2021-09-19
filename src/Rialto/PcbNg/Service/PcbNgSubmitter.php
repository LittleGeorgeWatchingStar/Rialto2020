<?php

namespace Rialto\PcbNg\Service;


use Gumstix\Storage\FileStorage;
use Rialto\Manufacturing\BuildFiles\PcbBuildFiles;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\PcbNg\Api\UserBoard;
use Rialto\PcbNg\Exception\PcbNgClientException;
use Rialto\PcbNg\Exception\PcbNgSubmitterException;
use Rialto\Purchasing\Order\PurchaseOrder;

class PcbNgSubmitter
{
    /** @var PcbNgClient */
    private $client;

    /** @var GerbersConverter */
    private $gerberConverter;

    /** @var LocationsConverter */
    private $locationsConverter;

    /** @var FileStorage */
    private $storage;

    public function __construct(PcbNgClient $client,
                                GerbersConverter $gerbersConverter,
                                LocationsConverter $locationsConverter,
                                FileStorage $storage)
    {
        $this->client = $client;
        $this->gerberConverter = $gerbersConverter;
        $this->locationsConverter = $locationsConverter;
        $this->storage = $storage;
    }

    /**
     * @param string $boardName Name of user board that will be created on PCB:NG
     * @throws PcbNgClientException
     * @throws PcbNgSubmitterException
     */
    public function submitPo(string $boardName, PurchaseOrder $order): UserBoard
    {
        if ($order->getSupplier() !== $this->client->getPcbNgSupplier()) {
            $pcbNgSupplierName = PcbNgClient::SUPPLIER_NAME;
            throw new PcbNgSubmitterException("The supplier for $order is not $pcbNgSupplierName.");
        }

        $lineItems = $order->getLineItems();
        foreach($lineItems as $lineItem) {
            if ($lineItem instanceof WorkOrder &&
                $lineItem->isBoard()) {
                return $this->submitWorkOrder($boardName, $lineItem);
            }
        }

        throw new PcbNgSubmitterException("$order does not contain a board.");
    }

    /**
     * @param string $boardName Name of user board that will be created on PCB:NG
     * @throws PcbNgSubmitterException
     * @throws PcbNgClientException
     */
    private function submitWorkOrder(string $boardName,
                                     WorkOrder $workOrder): UserBoard
    {

        $stockItem = $workOrder->getStockItem();
        $itemVersion = $stockItem->getVersion($workOrder->getVersion());

        $bomItems = $itemVersion->getBomItems();
        foreach ($bomItems as $bomItem) {
            if ($bomItem->getComponent()->isPCB()) {
                $buildFiles = PcbBuildFiles::create(
                    $bomItem->getComponent(),
                    $bomItem->getVersion(),
                    $this->storage);


                if (!$buildFiles->exists(PcbBuildFiles::GERBERS)) {
                    throw new PcbNgSubmitterException('Gerbers file not found.');
                }
                if (!$buildFiles->exists(PcbBuildFiles::BOARD_OUTLINE)) {
                    throw new PcbNgSubmitterException('Board outline gerber file not found.');
                }
                if (!$buildFiles->exists(PcbBuildFiles::DRILL_EXCELLON_24)) {
                    throw new PcbNgSubmitterException('Drill file not found.');
                }
                $gerbersZipData = $buildFiles->getContents(PcbBuildFiles::GERBERS);
                $boardOutlineData = $buildFiles->getContents(PcbBuildFiles::BOARD_OUTLINE);
                $drillData = $buildFiles->getContents(PcbBuildFiles::DRILL_EXCELLON_24);
                $gerbersZipData = $this->gerberConverter->convert($gerbersZipData, $boardOutlineData, $drillData);

                if (!$buildFiles->exists(PcbBuildFiles::XY)) {
                    throw new PcbNgSubmitterException('XY CSV not found.');
                }
                $locationsCsvData = $buildFiles->getContents(PcbBuildFiles::XY);
                $locationsCsvData = $this->locationsConverter->convert($locationsCsvData, $itemVersion);

                return $this->client->uploadBuildFilesAndCreateUserBoard(
                    $boardName,
                    $gerbersZipData,
                    $locationsCsvData,
                    $workOrder->getQtyOrdered());
            }
        }

        throw new PcbNgSubmitterException("PCB not found.");
    }
}