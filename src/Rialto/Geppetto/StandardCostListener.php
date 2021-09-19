<?php

namespace Rialto\Geppetto;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Manufacturing\Bom\BomEvent;
use Rialto\Manufacturing\ManufacturingEvents;
use Rialto\Purchasing\Catalog\Template\CustomBoardStrategy;
use Rialto\Purchasing\Catalog\Template\OmegaCustomBoardStrategy;
use Rialto\Purchasing\Catalog\Template\Orm\PurchasingDataTemplateRepository;
use Rialto\Purchasing\Catalog\Template\PurchasingDataTemplate;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Cost\StandardCost;
use Rialto\Stock\Cost\StandardCostUpdater;
use Rialto\Stock\Item\ManufacturedStockItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handles events which have implications for standard cost.
 */
class StandardCostListener implements EventSubscriberInterface
{
    /** @var StandardCostUpdater */
    private $updater;

    /** @var PurchasingDataTemplateRepository */
    private $repo;

    public function __construct(StandardCostUpdater $updater, ObjectManager $om)
    {
        $this->updater = $updater;
        $this->repo = $om->getRepository(PurchasingDataTemplate::class);
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     */
    public static function getSubscribedEvents()
    {
        return [
            ManufacturingEvents::NEW_BOM => ['setModuleStandardCost', -10],
        ];
    }

    /**
     * Updates the standard cost of Geppetto modules when the BOM changes.
     */
    public function setModuleStandardCost(BomEvent $event)
    {
        $version = $event->getItemVersion();
        $item = $version->getStockItem();
        if (! $item->isCategory(StockCategory::MODULE)) {
            return;
        }
        assertion($item instanceof ManufacturedStockItem);

        $stdCost = new StandardCost($item);
        $stdCost->setMaterialCost($version->getBom()->getTotalStandardCost());


        $labourCostArray = [];
        /** @var PurchasingDataTemplate[] $templates */
        $templates = $this->repo->findAll();
        foreach ($templates as $template) {
            $strategy = $template->getStrategyInstance();
            if ($strategy instanceof CustomBoardStrategy || $strategy instanceof OmegaCustomBoardStrategy) {
                $labourCostArray[] = $strategy->getModuleStandardLabourCost($template, $version);
            }
        }
        assertion(count($labourCostArray) > 0);
        $labourCost = array_sum($labourCostArray) / count($labourCostArray);
        $stdCost->setLabourCost($labourCost);

        $this->updater->update($stdCost);
    }
}
