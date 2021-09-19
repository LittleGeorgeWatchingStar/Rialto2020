<?php

namespace Rialto\Sales\Stats\Web;

use Rialto\Purchasing\LeadTime\LeadTimeCalculator;
use Rialto\Sales\Price\ProductPrice;
use Rialto\Sales\Stats\SalesStatMapper;
use Rialto\Sales\Stats\SalesStatOptions;
use Rialto\Sales\Type\SalesType;
use Rialto\Security\Role\Role;
use Rialto\Stock\Level\StockLevelService;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Generates a report of sales statistics and whether we have the stock
 * levels to keep up.
 */
class SalesStatsController extends RialtoController
{
    /**
     * @Route("/Sales/SalesStats", name="Sales_SalesStats")
     * @Method("GET")
     * @Template("sales/stats/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::CUSTOMER_SERVICE);
        $options = $this->createOptions();
        $filterForm = $this->createForm(SalesStatOptionsType::class, $options);
        $filterForm->handleRequest($request);
        if ($filterForm->isSubmitted() && (! $filterForm->isValid())) {
            return [
                'filterForm' => $filterForm->createView(),
                'stats' => [],
            ];
        }

        $hq = $this->getHeadquarters();
        $statMapper = new SalesStatMapper($this->dbm, $hq);
        $stats = $statMapper->findByOptions($options);
        $stats = $options->applyFilter($stats);

        return [
            'filterForm' => $filterForm->createView(),
            'stats' => $stats,
        ];
    }

    /** @return SalesStatOptions */
    private function createOptions()
    {
        $options = new SalesStatOptions();
        $options->setSalesType(SalesType::fetchOnlineSale($this->dbm));

        $stockLevels = $this->get(StockLevelService::class);
        $options->setStockLevels($stockLevels);

        $prices = $this->dbm->getRepository(ProductPrice::class);
        $options->setPrices($prices);

        $calculator = $this->get(LeadTimeCalculator::class);
        $options->setLeadTimeCalculator($calculator);

        return $options;
    }
}
