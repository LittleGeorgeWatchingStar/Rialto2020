<?php

namespace Rialto\Purchasing\Order\Command;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;

/**
 * Handler service for @see MergePurchaseOrdersCommand
 */
final class MergePurchaseOrdersHandler
{
    /** @var PurchaseOrderRepository */
    private $purchaseOrderRepo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->purchaseOrderRepo = $em->getRepository(PurchaseOrder::class);
    }

    public function handle(MergePurchaseOrdersCommand $command)
    {
        $primary = $this->purchaseOrderRepo->get($command->getPrimaryId());
        $secondary = $this->purchaseOrderRepo->get($command->getSecondaryId());

        $this->validateOrders($primary, $secondary);

        foreach ($secondary->getLineItems() as $item) {
            $secondary->removeLineItem($item);
            $primary->addItem($item);
        }

        $this->purchaseOrderRepo->delete($secondary);
    }

    private function validateOrders(PurchaseOrder $primary, PurchaseOrder $secondary): void
    {
        $primaryId = $primary->getId();
        $secondaryId = $secondary->getId();

        if ($primaryId == $secondaryId) {
            throw new \InvalidArgumentException(
                "Cannot merge PO \"$primaryId\" into itself."
            );
        }

        if ($primary->isSent()) {
            throw new \InvalidArgumentException(
                "PO \"$primaryId\" has already been sent."
            );
        }

        if ($secondary->isSent()) {
            throw new \InvalidArgumentException(
                "PO \"$secondaryId\" has already been sent."
            );
        }

        if ($primary->getSupplierId() != $secondary->getSupplierId()) {
            throw new \InvalidArgumentException(
                "PO \"$primaryId\" and \"$secondaryId\" have different suppliers."
            );
        }

        if ($primary->getDeliveryLocationId() != $secondary->getDeliveryLocationId()) {
            throw new \InvalidArgumentException(
                "PO \"$primaryId\" and \"$secondaryId\" have different delivery locations."
            );
        }
    }
}
