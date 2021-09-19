<?php

namespace Rialto\Sales\Invoice\Email;

use Rialto\Company\Company;
use Rialto\Email\Email;
use Rialto\Sales\Invoice\SalesInvoice;
use Rialto\Shipping\Shipment\SalesOrderShipment;

class SalesInvoiceAndShipmentEmail extends Email
{
    private $company;
    private $invoice;
    private $shipment;
    private $order;

    public function __construct(
        SalesInvoice $invoice,
        Company $company,
        SalesOrderShipment $shipment = null)
    {
        $this->invoice = $invoice;
        $this->shipment = $shipment;
        $this->order = $this->invoice->getSalesOrder();
        $this->company = $company;
    }

    public function prepare()
    {
        $this->setFrom($this->company);
        $this->addTo($this->invoice->getCustomer());
        $this->subject = sprintf('%s %s has been shipped',
            $this->company->getShortName(),
            $this->order->getSummaryWithCustomerRef());
        $this->template = 'sales/order/email/shipped.html.twig';
        $this->params = [
            'company' => $this->company,
            'order' => $this->order,
            'invoice' => $this->invoice,
            'shipper' => $this->invoice->getShipper(),
            'shipment' => $this->shipment,
        ];
    }

    public function setPdfData($data)
    {
        $filename = "Sales Invoice {$this->invoice->getInvoiceNumber()}.pdf";
        $this->addAttachmentFromFileData($data, 'application/pdf', $filename);
    }
}
