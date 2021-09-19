<?php

namespace Rialto\Purchasing\Web;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Catalog\PurchasingDataException;
use Rialto\Purchasing\Invoice\SupplierInvoiceFilesystem;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\EntityLinkExtension;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\GlobalsInterface;

/**
 * Twig extensions for the purchasing bundle.
 */
class PurchasingExtension extends EntityLinkExtension implements GlobalsInterface
{
    /** @var ObjectManager */
    private $om;

    /** @var PurchasingRouter */
    private $router;

    /** @var SupplierInvoiceFilesystem */
    private $filesystem;

    /** @var PurchasingDataRepository */
    private $purchasingDataRepo;

    public function __construct(ObjectManager $om,
                                PurchasingRouter $router,
                                AuthorizationCheckerInterface $auth,
                                SupplierInvoiceFilesystem $filesystem)
    {
        parent::__construct($auth);
        $this->om = $om;
        $this->router = $router;
        $this->filesystem = $filesystem;
        $this->purchasingDataRepo = $this->om->getRepository(PurchasingData::class);
    }

    public function getGlobals()
    {
        return [
            'rialto_purchasing_invoice_filesystem' => $this->filesystem,
        ];
    }

    public function getFilters()
    {
        return [
            $this->simpleFilter('cost_at_eoq', 'costAtEoq', ['html']),
            $this->simpleFilter('ext_cost_at_eoq', 'extCostAtEoq', ['html']),
            $this->simpleFilter('pd_staleness', 'staleness', ['html']),
            $this->simpleFilter('item_lead_time_prefered_purchasing_data', 'leadTime', ['html']),
        ];
    }


    public function leadTime(string  $sku)
    {
        $preferedPurchasingData = $this->purchasingDataRepo->queryPreferredByItemSku($sku);
        if ($preferedPurchasingData != null) {
            return $preferedPurchasingData->getLeadTimeAtEoq();
        } else {
            return "no preferred <br/> purchasing data";
        }

    }

    /**
     * Try to render the cost at EOQ, or show an error.
     */
    public function costAtEoq(PurchasingData $pd)
    {
        try {
            return number_format($pd->getCostAtEoq(), 4);
        } catch (PurchasingDataException $ex) {
            return sprintf('<div class="error">%s</div>',
                htmlentities($ex->getMessage()));
        }
    }

    /**
     * Try to render the extendent cost at EOQ, or show an error.
     */
    public function extCostAtEoq(PurchasingData $pd)
    {
        try {
            return number_format($pd->getCostAtEoq() * $pd->getEconomicOrderQty(), 2);
        } catch (PurchasingDataException $ex) {
            return sprintf('<div class="error">%s</div>',
                htmlentities($ex->getMessage()));
        }
    }

    /**
     * Return a string (suitable for CSS) indicating how "stale" a
     * purchasing data record is.
     */
    public function staleness(PurchasingData $pd)
    {
        $updated = $pd->getLastSync();
        if (null === $updated) {
            return 'very-stale';
        }
        $now = new \DateTime();
        $diff = $updated->diff($now);
        if ($diff->invert) {
            return '';
        }
        if ($diff->days >= 3) {
            return 'very-stale';
        } elseif ($diff->days >= 1) {
            return 'stale';
        }
        return '';
    }

    public function getFunctions()
    {
        return [
            $this->simpleFunction('supplier_link', 'supplierLink', ['html']),
            $this->simpleFunction('truncated_supplier_link', 'truncatedSupplierLink', ['html']),
            $this->simpleFunction('purchase_order_link', 'orderLink', ['html']),
            $this->simpleFunction('purchasing_data_link', 'purchasingDataLink', ['html']),
        ];
    }

    public function supplierLink(Supplier $supplier = null, $label = null)
    {
        if (!$supplier) {
            return $this->none();
        }
        $label = $label ?: $supplier->getName();
        $url = $this->router->supplierView($supplier);
        return $this->linkIfGranted(Role::EMPLOYEE, $url, $label, "_top");
    }

    public function truncatedSupplierLink(Supplier $supplier = null, $length = 6)
    {
        if (!$supplier) {
            return $this->none();
        }
        $label = $supplier->getName();
        $shortLabel = substr($label, 0, $length);
        $url = $this->router->supplierView($supplier);
        return $this->linkIfGranted(Role::EMPLOYEE, $url, $shortLabel, "_top");
    }

    public function orderLink(PurchaseOrder $order = null, $label = null)
    {
        if (!$order) {
            return $this->none();
        }
        $label = $label ?: $order->getId();
        $url = $this->router->orderView($order);
        return $this->linkIfGranted(Role::EMPLOYEE, $url, $label, "_top");
    }

    public function purchasingDataLink(PurchasingData $data = null, $label = null)
    {
        if (!$data) {
            return $this->none();
        }
        $label = $label ?: $data->getCatalogNumber();
        $url = $this->router->purchasingDataEdit($data);
        return $this->linkIfGranted(Role::PURCHASING_DATA, $url, $label, "_top");
    }
}

