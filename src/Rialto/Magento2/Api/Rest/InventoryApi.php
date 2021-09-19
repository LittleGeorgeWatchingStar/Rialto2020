<?php

namespace Rialto\Magento2\Api\Rest;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use Rialto\Stock\Item;
use Rialto\Stock\Level\AvailableStockLevel;

class InventoryApi extends BaseApi
{
    /**
     * Need to pass itemId (which is usually 1 unless you have multiple stocks
     * of an item) to the uri to update stock level.
     *
     * @see https://community.magento.com/t5/Programming-Questions/Magento-2-Rest-Api-to-Update-Stock/td-p/49249
     */
    const ITEM_ID = 1;

    /**
     * Update the stock level in Magento 2.
     *
     * Make sure that $status is up-to-date!
     */
    public function updateStockLevel(AvailableStockLevel $status)
    {
        assertion($this->store->getShipFromFacility()->equals($status->getLocation()));
        $data = [
            'stockItem' => [
                'qty' => $status->getQtyAvailable()
            ],
        ];
        $sku = $status->getSku();
        $itemId = self::ITEM_ID;
        try {
            $this->http->request('PUT', "rest/V1/products/$sku/stockItems/$itemId", [
                'json' => $data,
            ]);
        } catch (RequestException $ex) {
            if (!$this->isNotFound($ex)) {
                $this->logException($ex);
            }
        }
    }

    private function isNotFound(RequestException $ex): bool
    {
        $response = $ex->getResponse();
        return $response && ($response->getStatusCode() == 404);
    }

    /**
     * @return string[]
     * @throws TransferException
     */
    public function getStockLevel(Item $item): array
    {
        $sku = $item->getSku();
        $response = $this->request('GET', "rest/V1/stockItems/$sku");
        return $this->decodeBody($response);
    }
}
