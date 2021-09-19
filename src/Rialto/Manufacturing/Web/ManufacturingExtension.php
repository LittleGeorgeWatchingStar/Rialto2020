<?php

namespace Rialto\Manufacturing\Web;

use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Component\Component;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Security\Role\Role;
use Rialto\Stock\Sku;
use Rialto\Web\EntityLinkExtension;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Twig extensions for the manufacturing bundle.
 */
class ManufacturingExtension extends EntityLinkExtension
{
    /**
     * @var DbManager
     */
    private $dbm;

    /**
     * @var ManufacturingRouter
     */
    private $router;

    public function __construct(DbManager $dbm,
                                ManufacturingRouter $router,
                                AuthorizationCheckerInterface $auth)
    {
        parent::__construct($auth);
        $this->dbm = $dbm;
        $this->router = $router;
    }

    public function getFilters()
    {
        return [
            $this->simpleFilter(
                'rialto_manufacturing_extra_instructions',
                'extraInstructions', ['tex']),
        ];
    }

    public function extraInstructions(
        Component $component,
        WorkOrder $order)
    {
        if ($this->isLabel($component)) {
            return sprintf('Use labels marked "%s"',
                $order->getStockItem()->getName());
        }
        $suffix = $component->getVersion()->getStockCodeSuffix();
        if ($suffix) {
            return "Use version $suffix";
        }
        return null;
    }

    private function isLabel(Component $component)
    {
        /* This is real ugly, but I'm at a loss for how better to handle
         * the "magic" business logic associated with printed labels. */
        return $component->getSku() === Sku::PRINTED_LABEL;
    }

    public function getFunctions()
    {
        return [
            $this->simpleFunction("work_order_link", 'workOrderLink', ['html']),
            $this->simpleFunction('find_original_build', 'findOriginalBuild', []),
        ];
    }

    public function workOrderLink(WorkOrder $wo = null, $label = null)
    {
        if (!$wo) {
            return $this->none();
        }
        $label = $label ?: $wo->getId();
        $url = $this->router->workOrderView($wo);
        return $this->linkIfGranted(Role::EMPLOYEE, $url, $label, "_top");
    }

    /**
     * Finds the original build order for the given rework order.
     *
     * @return WorkOrder|null
     */
    public function findOriginalBuild(StockProducer $poItem)
    {
        if (!$poItem->isWorkOrder()) {
            return null;
        }
        if (!$poItem->isRework()) {
            return null;
        }
        return $this->dbm->getRepository(WorkOrder::class)
            ->findOriginalBuild($poItem);
    }

}
