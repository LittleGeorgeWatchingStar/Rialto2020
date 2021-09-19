<?php

namespace Rialto\Sales\Order\Web;


use Gumstix\Filetype\CsvFileWithHeadings;
use Gumstix\GeographyBundle\Model\PostalAddress;
use Gumstix\GeographyBundle\Service\AddressFormatter;
use Rialto\Sales\Order\SalesOrder;

class SalesOrderCsv
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param $orders SalesOrder[]
     * @return CsvFileWithHeadings
     */
    public static function create($orders)
    {
        $rows = [];
        foreach ($orders as $order) {
            foreach ($order->getLineItems() as $item) {
                $rows[] = [
                    'id' => $order->getId(),
                    'sku' => $item->getSku(),
                    'netPrice' => $item->getExtendedPrice(),
                    'qtyOrdered' => $item->getQtyOrdered(),
                    'qtyInvoiced' => $item->getQtyInvoiced(),
                    'targetShipDate' => $order->getTargetShipDate() ? $order->getTargetShipDate()->format('Y-m-d') : '',
                    'customerRef' => $order->getCustomerReference(),
                    'dateOrdered' => $order->getDateOrdered()->format(self::DATE_FORMAT),
                    'shipDate' => $order->getDateToShip() ? $order->getDateToShip()->format(self::DATE_FORMAT) : '',
                    'orderType' => $order->getSalesType(),
                    'billingCompany' => $order->getBillingCompany(),
                    'billingName' => $order->getBillingName(),
                    'billingAddress' => self::formatAddress($order->getBillingAddress()),
                    'shippingCompany' => $order->getDeliveryCompany(),
                    'shippingName' => $order->getDeliveryName(),
                    'shippingAddress' => self::formatAddress($order->getDeliveryAddress()),
                    'totalAmount' => $order->getTotalPrice(),
                ];
            }
        }

        $csv = new CsvFileWithHeadings();
        $csv->parseArray($rows);
        return $csv;
    }

    private static function formatAddress(PostalAddress $address)
    {
        $formatter = new AddressFormatter();
        return $formatter->toString($address);
    }
}
