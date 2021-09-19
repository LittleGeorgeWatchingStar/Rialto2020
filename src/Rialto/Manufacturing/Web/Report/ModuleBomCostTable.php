<?php


namespace Rialto\Manufacturing\Web\Report;


use Rialto\Database\Orm\DoctrineDbManager;
use Rialto\Purchasing\Catalog\Template\CustomBoardStrategy;
use Rialto\Purchasing\Catalog\Template\PurchasingDataTemplate;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\Report\AbstractAudit;

class ModuleBomCostTable extends AbstractAudit
{

    public function fetchResults(DoctrineDbManager $dbm, array $params = [])
    {
        $repo = $dbm->getRepository(StockItem::class);
        $strats = $dbm->getRepository(PurchasingDataTemplate::class);

        /** @var StockItem[] $modules */
        $modules = $repo->queryByFilters([
            'category' => StockCategory::MODULE,
        ])->getResult();

        $results = [];
        foreach ($modules as $module) {
            $version = $module->getAutoBuildVersion();
            $calculatedMaterials = $version->getTotalStandardCost();
            $numComponents = $version->getTotalNumberOfComponents();
            $componentTemplates = $strats->findTemplatesForStrategy(CustomBoardStrategy::STRATEGY_NAME);
            $componentCosts = array_map(function (PurchasingDataTemplate $template) {
                return $template->getVariables()['unitCost'];
            }, $componentTemplates);
            $componentCost = array_sum($componentCosts)/count($componentCosts);
            $calculatedLabour = $numComponents * $componentCost;
            $calculatedStandard = $calculatedMaterials + $calculatedLabour;
            $results[] = [
                'SKU' => $module->getSku(),
                'Version' => $version->getVersionCode(),
                'Distinct Components' => count($version->getBomItems()),
                'Total Components' => $version->getTotalNumberOfComponents(),
                'Material Cost' => $module->getMaterialCost(),
                'Calculated Material Cost' => $calculatedMaterials,
                'Labour Cost' => $module->getLabourCost(),
                'Calculated Labour Cost' => $calculatedLabour,
                'Standard Cost' => $module->getStandardCost(),
                'Calculated Standard Cost' => $calculatedStandard,
            ];
        }

        return $results;
    }

    public function supportsParameter($paramName)
    {
        return false;
    }
}
