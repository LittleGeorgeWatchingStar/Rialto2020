<?php

namespace Rialto\Sales\Order\Email;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Gumstix\Storage\FileStorage;
use Rialto\Manufacturing\BuildFiles\BuildFiles;
use Rialto\Manufacturing\BuildFiles\PcbBuildFiles;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Shipping\Export\AllowedCountry;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Orm\ItemVersionRepository;

/**
 * filter to determine whether an order paid is new
 * and an email needs to be sent to the relevant parties
 *
 * @AllowedCountry
 */
class OrderToEmailFilter
{
    /** @var FileStorage */
    private $fileStorage;

    /** @var ItemVersionRepository */
    private $itemVersionRepo;

    public function __construct(FileStorage $fs, EntityManagerInterface $em)
    {
        $this->fileStorage = $fs;
        /** @var EntityRepository $repo */
        $repo = $em->getRepository(ItemVersion::class);
        $this->itemVersionRepo = $repo;
    }

    /**
     * Determine whether a stock item is new and needs to
     * notify the relevant parties
     *
     * @param SalesOrder $salesOrder
     * @return bool
     *  True for need to send email
     *  False for not need to sendresult
     */
    public function IsEmailNecessary(SalesOrder $salesOrder)
    {
        $needToSendEmail = false;
        $salesOrderDetails = $salesOrder->getLineItems();
        foreach ($salesOrderDetails as $orderDetail) {
            $item = $orderDetail->getStockItem();
            $sku = $item->getSku();
            $subsku = substr($sku, 0, 4);
            $version = $orderDetail->getVersion();
            $itemVersion = $this->itemVersionRepo->findOneBy([
                'stockItem' => $sku,
                'version' => $version->getVersionCode(),
            ]);
            if ($subsku == "PKG9" && $itemVersion) {
                /** @var ItemVersion $itemVersion */
                $needToSendEmail = $this->searchForPCB($itemVersion);
            } elseif ($subsku == "BRD9" && $itemVersion) {
                return true;
            }
        }

        return $needToSendEmail;
    }

    /**
     * search for build files for PCB
     *
     * @param ItemVersion $iv
     * @return bool
     * False if the build files already exist or none need to be created.
     * True if new build files are required.
     */
    public function searchForPCB(ItemVersion $iv)
    {
        $result = false;
        $bomItems = $iv->getBomItems();
        foreach ($bomItems as $bomItem) {
            $version = $bomItem->getVersion();
            $item = $bomItem->getStockItem();
            $sku = $item->getSku();
            $subsku = substr($sku, 0, 4);
            $itemVersion = $this->itemVersionRepo->findOneBy([
                'stockItem' => $sku,
                'version' => $version->getVersionCode(),
            ]);
            if ($subsku == "BRD9") {
                /** @var ItemVersion $itemVersion */
                return true;
            }
        }
        return $result;
    }


}
