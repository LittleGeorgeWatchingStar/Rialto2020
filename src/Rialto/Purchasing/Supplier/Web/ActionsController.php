<?php

namespace Rialto\Purchasing\Supplier\Web;

use Psr\Container\ContainerInterface;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;


/**
 * Renders the links that appear on the supplier details page.
 */
class ActionsController extends RialtoController
{
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    public function linksAction(Supplier $supplier)
    {
        $links = [
            'Inquiries' => $this->inquiryLinks($supplier),
            'Maintenance' => $this->maintenanceLinks($supplier),
            'Transactions' => $this->transactionLinks($supplier),
        ];
        return $this->render('purchasing/supplier/actions.html.twig', [
            'supplier' => $supplier,
            'links' => $links,
        ]);
    }

    private function inquiryLinks(Supplier $supplier)
    {
        $supplierID = $supplier->getId();
        $recentDate = new \DateTime('-6 months');

        $links = [];
        if ($this->isGranted(Role::ACCOUNTING)) {
            $links["Supplier transactions"] = $this->generateUrl('supplier_transaction_list', [
                'supplier' => $supplierID,
                'dates' => ['start' => $recentDate->format('Y-m-d')],
            ]);
        }

        $links["Outstanding purchase orders"] = $this->generateUrl('purchase_order_list', [
            "supplier" => $supplierID,
            "completed" => "no",
        ]);
        $links["Recent invoices"] = $this->generateUrl('supplier_invoice_list', [
            'supplier' => $supplierID,
            'since' => $recentDate->format('Y-m-d'),
        ]);

        if ($supplier->getFacility()) {
            $links['Dashboard'] = $this->generateUrl(
                'supplier_order_list', ['id' => $supplierID]
            );
        }

        if ($this->isGranted(Role::ACCOUNTING)) {
            $links['Uninvoiced inventory'] = $this->generateUrl('Core_Audit_report', [
                'module' => 'Accounting',
                'auditName' => 'UninvoicedInventory',
                'supplier' => $supplierID,
            ]);
        }

        return $links;
    }

    private function maintenanceLinks(Supplier $supplier)
    {
        $supplierID = $supplier->getId();
        $links = [];

        if ($this->isGranted(Role::PURCHASING)) {
            $links["Edit this supplier"] = $this->generateUrl('supplier_edit', [
                "id" => $supplierID
            ]);
            $links["Add a new contact"] = $this->generateUrl("Purchasing_SupplierContact_create", [
                "supplierId" => $supplierID
            ]);

            $links["Upload a quotation"] = $this->generateUrl(
                'Purchasing_PurchasingData_upload', [
                'id' => $supplierID,
            ]);
        }
        if ($this->isGranted(Role::ADMIN)) {
            $links["Edit email invoice pattern"] = $this->generateUrl(
                'Purchasing_SupplierInvoicePattern_edit', [
                'id' => $supplierID,
            ]);
            $links["Edit attributes"] = $this->generateUrl('supplier_attribute_edit', [
                'id' => $supplierID,
            ]);
        }

        return $links;
    }

    private function transactionLinks(Supplier $supplier)
    {
        $supplierID = $supplier->getId();
        $links = [];

        if ($this->isGranted(Role::PURCHASING)) {
            $links["Create a purchase order"] = $this->generateUrl(
                'Purchasing_PurchaseOrder_create'
            );
        }

        if ($this->isGranted(Role::ACCOUNTING)) {
            $links["Enter a supplier invoice"] = $this->generateUrl(
                'Purchasing_SupplierInvoice_fromSupplier', [
                'id' => $supplierID,
            ]);
            $links["Enter a suppliers credit note"] = $this->generateUrl(
                'Accounting_Supplier_creditNote', [
                'id' => $supplierID,
            ]);
            $links["Enter a payment to the supplier"] = $this->generateUrl(
                'Accounting_SupplierPayment_create', [
                'id' => $supplierID,
            ]);
            $links["Enter a refund from the supplier"] = $this->generateUrl(
                'Accounting_SupplierRefund_create', [
                'id' => $supplierID,
            ]);
            $links["Cancel a check"] = $this->generateUrl('banktransaction_list', [
                'type' => BankTransaction::TYPE_CHEQUE,
                'cleared' => 'no',
                'supplier' => $supplierID
            ]);
            $links["Approve invoices"] = $this->generateUrl('Purchasing_SupplierInvoice_approvalList', [
                'supplier' => $supplierID
            ]);
        }

        if ($this->isGranted(Role::ADMIN)) {
            $recentDate = new \DateTime();
            $recentDate->modify('-1 month');
            $links["Reverse an outstanding GRN"] = $this->generateUrl('grn_list', [
                'supplier' => $supplierID,
                'startDate' => $recentDate->format('Y-m-d'),
            ]);
        }

        return $links;
    }

}
