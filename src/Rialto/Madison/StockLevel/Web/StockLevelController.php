<?php

namespace Rialto\Madison\StockLevel\Web;


use FOS\RestBundle\View\View;
use Rialto\Security\Role\Role;
use Rialto\Stock\Level\AvailableStockLevel;
use Rialto\Stock\Level\Orm\StockLevelStatusRepository;
use Rialto\Stock\Level\StockLevelStatus;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * For letting Madison know about product stock levels.
 */
class StockLevelController extends RialtoController
{
    /**
     * @Route("/api/v2/stock/product-stock-levels/")
     * @api for Madison
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        /** @var $repo StockLevelStatusRepository */
        $repo = $this->getRepository(StockLevelStatus::class);
        $hq = $this->getHeadquarters();
        /** @var $levels AvailableStockLevel[] */
        $levels = $repo->createBuilder()
            ->byLocation($hq)
            ->isActiveLocation()
            ->sellableItems()
            ->getResult();
        $result = [];
        foreach ($levels as $level) {
            $result[$level->getSku()] = $level->getQtyAvailable();
        }
        return View::create($result);
    }
}
