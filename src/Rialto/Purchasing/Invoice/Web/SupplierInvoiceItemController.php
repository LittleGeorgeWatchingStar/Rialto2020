<?php

namespace Rialto\Purchasing\Invoice\Web;

use Rialto\Purchasing\Invoice\SupplierInvoiceItem;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Controller for managing supplier invoice line items.
 */
class SupplierInvoiceItemController extends RialtoController
{
    /**
     * @Route("/record/Purchasing/SupplierInvoiceItem/{id}",
     *   name="Purchasing_SupplierInvoiceItem_delete")
     * @Method("DELETE")
     */
    public function deleteAction(SupplierInvoiceItem $item)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $invoice = $item->getSupplierInvoice();
        $id = $item->getId();
        $this->dbm->remove($item);
        $this->dbm->flush();
        $this->logNotice("Deleted invoice item $id.");
        return $this->redirectToRoute('supplier_invoice_view', [
            'id' => $invoice->getId(),
        ]);
    }

}
