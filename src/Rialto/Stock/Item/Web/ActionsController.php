<?php

namespace Rialto\Stock\Item\Web;

use Psr\Container\ContainerInterface;
use Rialto\Purchasing\Order\StockItemVoter;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\DummyStockItem;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Publication\Orm\PublicationRepository;
use Rialto\Stock\Publication\Publication;
use Rialto\Web\RialtoController;


/**
 * Renders the links that appear on the stock item details page.
 */
class ActionsController extends RialtoController
{
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    public function linksAction(StockItem $item)
    {
        $links = [
            'Inquiries' => $this->itemInquireList($item),
            'Maintenance' => $this->itemMaintenanceList($item),
            'Transactions' => $this->itemTransactionList($item),
        ];
        return $this->render('stock/item/actions.html.twig', [
            'stockItem' => $item,
            'links' => $links,
            'publications' => $this->findPublications($item),
        ]);
    }

    private function itemInquireList(StockItem $item)
    {
        $sku = $item->getSku();

        $links = [];
        if ($item instanceof PhysicalStockItem) {
            $links["Bin list"] = $this->generateUrl('stock_bin_list', [
                "stockItem" => $sku,
            ]);
        }
        if (!$item instanceof DummyStockItem) {
            $links["Stock movements"] = $this->generateUrl('stock_move_list', [
                'item' => $sku,
                'startDate' => date('Y-m-d', strtotime('-1 week')),
            ]);
            $links["Most recent moves by bin"] = $this->generateUrl('Core_Audit_report', [
                'module' => 'Stock',
                'auditName' => 'BinLastMove',
                "stockCode" => $sku,
            ]);
        }
        $links["Stock allocations"] = $this->generateUrl(
            'allocation_list', [
            'stockItem' => $sku,
        ]);
        $links["Outstanding sales orders"] = $this->generateUrl('sales_order_list', [
            "item" => $sku,
            'shipped' => 'no',
        ]);
        $links["Completed sales orders"] = $this->generateUrl('sales_order_list', [
            "item" => $sku,
            'shipped' => 'yes',
        ]);
        $links['Change notices'] = $this->generateUrl('change_notice_list', [
            'stockItem' => $sku,
        ]);

        if ($item->isManufactured()) {
            $links["Outstanding work orders"] = $this->generateUrl('workorder_list', [
                'closed' => 'no',
                "stockItem" => $sku,
            ]);
        } elseif ($item->isPurchased()) {
            $links["Outstanding purchase orders"] = $this->generateUrl(
                'purchase_order_list', [
                "stockItem" => $sku,
                "completed" => "no",
            ]);
            $links["All purchase orders"] = $this->generateUrl(
                'purchase_order_list', [
                "stockItem" => $sku
            ]);
        }

        if (!$item instanceof DummyStockItem) {
            $links["Stock status"] = $this->generateUrl('Stock_StockLevel_list', [
                'stockCode' => $sku
            ]);
            $links['Lead time'] = $this->generateUrl('Purchasing_LeadTime', [
                'stockItem' => $sku,
            ]);
        }
        if ($item->isPhysicalPart()) {
            $links["Stock usage"] = $this->generateUrl('Core_Audit_report', [
                'module' => 'Stock',
                'auditName' => 'StockUsage',
                "stockCode" => $sku
            ]);
            $links["Where this item is used"] = $this->generateUrl('item_version_list', [
                "component" => $sku,
                '_limit' => 0,
            ]);
        }

        if ($item->hasSubcomponents()) {
            $version = $item->getAutoBuildVersion();
            $links["View costed bill of material"] = $this->generateUrl('item_version_edit', [
                'item' => $version->getSku(),
                'version' => $version->getVersionCode()
            ]);
        }
        if ($item->isBoard()) {
            $links["CMRT summary"] = $this->generateUrl('Core_Audit_report', [
                'module' => 'Purchasing',
                'auditName' => 'CmrtReport',
                'sku' => $item->getSku(),
            ]);
        }
        return $links;
    }

    private function itemMaintenanceList(StockItem $item)
    {
        $sku = $item->getSku();

        $links = [];
        $links["Edit $sku"] = $this->generateUrl(
            'Stock_StockItem_edit', [
            'stockCode' => $sku
        ]);
        $links["Publications"] = $this->generateUrl(
            'stock_publication_list', [
            'stockItem' => $sku,
        ]);
        $links["Features"] = $this->generateUrl('stock_item_edit_features', [
            'stockCode' => $sku,
        ]);
        $links["Connections and flags"] = $this->generateUrl(
            'stock_item_connections', [
            'stockCode' => $sku
        ]);
        $links['Attributes'] = $this->generateUrl('stock_item_attribute_edit', [
            'id' => $sku,
        ]);
        $links["Clone this item"] = $this->generateUrl('Stock_StockItem_clone', [
            'stockCode' => $sku,
        ]);

        if ($item->isSellable()) {
            $links["Pricing"] = $this->generateUrl(
                'Sales_ProductPrice_list', [
                "stockCode" => $sku
            ]);
        }

        if ($item->isBoard()) {
            $links["Create a packaged product"] = $this->generateUrl(
                'stock_item_create_package', [
                'stockCode' => $sku,
            ]);
        }

        if ($item->isPhysicalPart()) {
            $links["Order points"] = $this->generateUrl('Stock_OrderPoint_edit', [
                'sku' => $sku,
            ]);
            $links["Standard cost"] = $this->generateUrl('Stock_StandardCost_update', [
                "stockCode" => $sku
            ]);
            if ($this->isGranted(Role::PURCHASING)) {
                $links["Purchasing data"] = $this->generateUrl('purchasing_data_list', [
                    "stockItem" => $sku
                ]);
            }
            $links["Print labels"] = $this->generateUrl('stock_bin_list', [
                "stockItem" => $sku
            ]);
            $links["Edit reels"] = $this->generateUrl('stock_bin_list', [
                "stockItem" => $sku
            ]);
        }

        if ($item->hasSubcomponents()) {
            $links["Customizations"] = $this->generateUrl('customization_list', [
                'sku' => $sku,
                'active' => 'yes',
            ]);
        }

        if ($item->isPCB() && $item->getAutoBuildVersion()->isValid()) {
            $links['Upload build files'] = $this->generateUrl(
                'Manufacturing_BuildFiles_upload', [
                'stockCode' => $sku,
                'version' => (string) $item->getAutoBuildVersion(),
            ]);
        }

        return $links;
    }

    private function itemTransactionList(StockItem $item)
    {
        $sku = $item->getSku();
        $links = [];

        if ($item->isPhysicalPart() && $this->isGranted(Role::WAREHOUSE)) {
            $links["Location transfer"] = $this->generateUrl('stock_transfer_create', [
                'item' => $sku,
                'source' => $this->getHeadquarters()->getId(),
            ]);
        }

        if ($this->isGranted(StockItemVoter::PURCHASE, $item)) {
            if ($item->isManufactured()) {
                $links["Create a work order"] = $this->generateUrl(
                    'work_order_create', [
                    'stockItem' => $sku,
                ]);
                $links["Create a rework order"] = $this->generateUrl('rework_from_bins', [
                    'stockCode' => $sku,
                ]);
            } elseif ($item->isPurchased()) {
                $links["Create a purchase order"] = $this->generateUrl(
                    'Purchasing_PurchaseOrder_singleItem', [
                    "stockCode" => $sku,
                ]);
            }
        }

        return $links;
    }

    /** @return Publication[] */
    private function findPublications(StockItem $item)
    {
        /** @var $repo PublicationRepository */
        $repo = $this->getRepository(Publication::class);
        return $repo->createBuilder()
            ->byItem($item)
            ->isUrl()
            ->getResult();
    }
}
