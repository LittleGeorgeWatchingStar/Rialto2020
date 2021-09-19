<?php

namespace Rialto\Purchasing\Producer\Web;

use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Security\Privilege;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * For managing PO line items and work orders.
 */
class StockProducerController extends RialtoController
{
    /**
     * @Route("/record/Purchasing/StockProducer/{id}/",
     *   name="Purchasing_StockProducer_delete")
     * @Method("DELETE")
     */
    public function deleteAction(StockProducer $poItem)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $po = $poItem->getPurchaseOrder();
        if ( $this->isGranted(Privilege::DELETE, $poItem) ) {
            $this->dbm->beginTransaction();
            try {
                if ($poItem instanceof WorkOrder) {
                    $child = $poItem->getChild();
                    if ($child) {
                        $child->setParent(null);
                    }
                }
                $this->dbm->flush();
                $this->dbm->remove($poItem);
                $this->dbm->flushAndCommit();
                $this->logNotice("Deleted $poItem");
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        } else {
            $poItem->cancel();
            $this->logNotice("Cancelled $poItem.");
        }
        $po->setUpdated();
        $this->dbm->flush();
        return $this->redirectToRoute('purchase_order_view', [
            'order' => $po->getId(),
        ]);
    }

    /**
     * @Route("/Purchasing/StockProducer/{id}/reopen/",
     *   name="Purchasing_StockProducer_reopen")
     * @Method("PUT")
     */
    public function reopenAction(StockProducer $poItem)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $poItem->reopen();
        $this->dbm->flush();

        if ( $poItem->isClosed() ) {
            $this->logWarning("$poItem cannot be reopened.");
        } else {
            $this->logNotice("Reopened $poItem successfully.");
        }
        return $this->redirectToRoute('purchase_order_view', [
            'order' => $poItem->getOrderNumber(),
        ]);
    }
}
