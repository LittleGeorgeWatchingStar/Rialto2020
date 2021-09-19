<?php

namespace Rialto\Manufacturing\Requirement;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Manufacturing\Bom\Orm\TurnkeyExclusionRepository;
use Rialto\Manufacturing\Bom\TurnkeyExclusion;
use Rialto\Manufacturing\Component\Component;
use Rialto\Manufacturing\Customization\CustomizationErrorHandler;
use Rialto\Manufacturing\Customization\Customizer;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Stock\ItemIndex;


/**
 * Creates and updates the requirements for a work order.
 */
class RequirementFactory
{
    /** @var ScrapCalculator */
    private $scrapCalculator;

    /** @var TurnkeyExclusionRepository */
    private $turnkeyRepo;

    /** @var Customizer */
    private $customizer;

    /** @var CustomizationErrorHandler */
    private $errorHandler;

    public function __construct(ObjectManager $om,
                                ScrapCalculator $calculator,
                                Customizer $customizer,
                                CustomizationErrorHandler $handler)
    {
        $this->scrapCalculator = $calculator;
        $this->turnkeyRepo = $om->getRepository(TurnkeyExclusion::class);
        $this->customizer = $customizer;
        $this->errorHandler = $handler;
    }

    public function updateRequirements(WorkOrder $workOrder)
    {
        if ($workOrder->isIssued()) {
            $msg = "Cannot delete requirements for issued $workOrder";
            throw new \InvalidArgumentException($msg);
        } elseif ($workOrder->isRework()) {
            /* Rework orders have completely custom requirements */
            return;
        }

        $bom = $workOrder->getCustomizedBom($this->customizer);
        $errors = $this->customizer->getErrors();
        $this->errorHandler->handleErrors($workOrder, $errors);
        $this->removeTurnkeyItems($workOrder, $bom);
        $workOrder->resetRequirements($bom);
        $workOrder->setChildRequirementVersionAndCustomization();
        $this->scrapCalculator->updateScrapCounts($workOrder);
    }

    private function removeTurnkeyItems(WorkOrder $workOrder, ItemIndex $bom)
    {
        foreach ($bom as $component) {
            if ($this->isTurnkey($workOrder, $component)) {
                $bom->remove($component);
            }
        }
    }

    private function isTurnkey(WorkOrder $workOrder, Component $component)
    {
        return $workOrder->isTurnkey()
        && $this->turnkeyRepo->isTurnkeyComponent($workOrder, $component);
    }

    /**
     * Generate requirements for all work orders in $po.
     */
    public function forPurchaseOrder(PurchaseOrder $po)
    {
        foreach ($po->getWorkOrders() as $wo) {
            $this->updateRequirements($wo);
        }
    }
}

