<?php

namespace Rialto\Madison\StockItem\Web;

use FOS\RestBundle\View\View;
use Rialto\Ciiva\ApiDto\GetSupplierComponentsByPartNumberForAltiumRequest;
use Rialto\Ciiva\ApiDto\GetSupplierComponentsByPartNumberForAltiumResponse;
use Rialto\Ciiva\CiivaClient;
use Rialto\Madison\StockItem\CompatibleProductCalculator;
use Rialto\Madison\StockItem\ComponentsOfInterestCalculator;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StockItemController extends RialtoController
{
    /** @var CiivaClient */
    private $ciiva;

    public function init(ContainerInterface $container)
    {
        $this->ciiva = $this->get(CiivaClient::class);
    }

    /**
     * Get the key components of a stock item.
     *
     * @Route("/api/stock/item/{item}/key-components/")
     * @Route("/api/v2/stock/item/{item}/key-components/")
     * @api for Madison
     */
    public function getKeyComponentsAction(StockItem $item)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $calculator = new ComponentsOfInterestCalculator($this->dbm);
        $components = array_values($calculator->getComponents($item));
        $included = $calculator->getIncludedItems($item);
        return View::create(array_merge($components, $included));
    }

    /**
     * Get the compatible products and packs of $item.
     *
     * @Route("/api/stock/item/{item}/related-products/")
     * @Route("/api/v2/stock/item/{item}/related-products/")
     * @api for Madison
     */
    public function getRelatedProductsAction(StockItem $item)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);

        $data = [];

        $calculator = new CompatibleProductCalculator($this->dbm);
        $compatible = $calculator->findCompatibleProducts($item);
        foreach ($compatible as $product) {
            $data[] = [
                'sku' => $product->getSku(),
                'stockCode' => $product->getSku(), // deprecated
                'name' => $product->getName(),
                'relationship' => 'compatible',
            ];
        }

        /* @var $repo StockItemRepository */
        $repo = $this->getRepository(StockItem::class);
        $packs = $repo->findPacks($item);
        foreach ($packs as $product) {
            $data[] = [
                'sku' => $product->getSku(),
                'stockCode' => $product->getSku(), // deprecated
                'name' => $product->getName(),
                'relationship' => 'pack',
            ];
        }
        return View::create($data);
    }

    /**
     * Get the manufacturer's image of $item
     *
     * @Route("/api/v2/stock/item/{item}/image/")
     */
    public function getImageAction(StockItem $item)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);

        $primaryItem = $this->getPrimaryComponent($item);
        if (!$primaryItem) {
            throw $this->notFound(
                "{$item->getName()} does not have a primary component with purchasing data.");
        }

        $purchData = $primaryItem->getPreferredPurchasingData();

        if (!$purchData) {
            throw $this->notFound(
                "{$primaryItem->getName()} has no purchasing data.");
        }

        $request = new GetSupplierComponentsByPartNumberForAltiumRequest(
            $purchData->getSupplierName(),
            $purchData->getCatalogNumber()
        );
        /** @var GetSupplierComponentsByPartNumberForAltiumResponse $response */
        $response = $this->ciiva->post($request);

        $pairs = $response->getComponentPairs();
        $manufacturerComponent = $pairs[0]->getManufacturerComponent();

        return View::create([
            'url' => $manufacturerComponent->getManufacturerPartImageUrl()
        ]);
    }

    /**
     * @return StockItem|null
     */
    private function getPrimaryComponent(StockItem $item)
    {
        $version = $item->getAutoBuildVersion();
        if ($bom = $version->getBom()) {
            foreach ($bom->getItems() as $bomItem) {
                if ($bomItem->isPrimary()) {
                    return $bomItem->getStockItem();
                }
            }
            return null;
        } else {
            return $item;
        }
    }
}
