<?php

namespace Rialto\Magento2\Api\Rest;

use Rialto\Sales\Invoice\SalesInvoice;

class ShipmentApi extends BaseApi
{
    public function createShipment(SalesInvoice $invoice)
    {
        $itemQuantities = [];

        foreach ($invoice->getLineItems() as $item) {
            $sourceID = $item->getSourceId();
            $qtyInvoiced = $item->getQtyInvoiced();
            if (!$sourceID) {
                return;
            }
            if ($qtyInvoiced > 0) {
                $itemQuantities[] = [
                    'order_item_id' => $sourceID,
                    'qty' => $qtyInvoiced,
                    'sku' => $item->getSku(),
                    'name' => $item->getDescription(),
                ];
            }
        }

        $requestBody = [
            "entity" => [
                "order_id" => $invoice->getSourceId(),
                "items" => $itemQuantities,
            ]
        ];

        if ($invoice->getTrackingNumber()) {
            $shipperName = $invoice->getShipper()->getName();
            $trackingInfo = [
                [
                    "order_id" => $invoice->getSourceId(),
                    "weight" => $invoice->getCalculatedWeight(),
                    "description" => $invoice->getComments(),
                    "track_number" => $invoice->getTrackingNumber(),
                    "title" => "$shipperName tracking number",
                    "carrier_code" => strtolower($shipperName)
                ],
            ];
            $requestBody["entity"]["tracks"] = $trackingInfo;
        }

        $this->request('POST', "rest/V1/shipment", [
            'json' => $requestBody,
        ]);
    }
}
