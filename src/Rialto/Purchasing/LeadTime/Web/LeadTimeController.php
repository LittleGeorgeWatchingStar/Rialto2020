<?php

namespace Rialto\Purchasing\LeadTime\Web;

use FOS\RestBundle\View\View;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Catalog\PurchasingDataException;
use Rialto\Purchasing\LeadTime\LeadTime;
use Rialto\Purchasing\LeadTime\LeadTimeCalculator;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\InvalidItemException;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Error\RuntimeError;

/**
 * Generates reports about stock item lead time.
 */
class LeadTimeController extends RialtoController
{
    /**
     * @var LeadTimeCalculator
     */
    private $calculator;

    protected function init(ContainerInterface $container)
    {
        $this->calculator = $this->get(LeadTimeCalculator::class);
    }

    /**
     * Shows an analysis of the lead time for the requested item or
     * work order.
     *
     * @Route("/Purchasing/LeadTime", name="Purchasing_LeadTime")
     * @Method("GET")
     */
    public function reportAction(Request $request)
    {
        try {
            $leadTime = $this->getLeadTime($request);
            return $this->render('purchasing/leadtime/report.html.twig', [
                'leadTime' => $leadTime,
            ]);
        } catch (InvalidItemException $ex) {
            return $this->handleInvalidItem($ex);
        } catch (RuntimeError $ex) {
            $previous = $ex->getPrevious();
            if ($previous instanceof InvalidItemException) {
                return $this->handleInvalidItem($previous);
            }
            throw $ex;
        }
    }

    private function handleInvalidItem(InvalidItemException $ex)
    {
        $stockCode = $ex->getStockCode();
        $uri = $this->generateUrl('purchasing_data_list', [
            'stockItem' => $stockCode,
        ]);
        return $this->render('error.html.twig', [
            'message' => $ex->getMessage(),
            'uri' => $uri,
            'linkText' => "Click here to view the purchasing data for $stockCode.",
        ]);
    }

    /** @return LeadTime */
    private function getLeadTime(Request $request)
    {
        if ($request->get('stockItem')) {
            $item = $this->needEntity(StockItem::class, $request->get('stockItem'));
            return $this->calculator->forStockItem($item, $request->get('orderQty'));
        } elseif ($request->get('workOrder')) {
            $workOrder = $this->needEntity(
                WorkOrder::class,
                $request->get('workOrder'));
            return $this->calculator->forWorkOrder($workOrder);
        } else {
            throw $this->badRequest();
        }
    }

    /**
     * API view of the lead time for a single item.
     *
     * @api for Madison
     *
     * @Route("/api/v2/purchasing/lead-time/{item}/")
     * @Method("GET")
     */
    public function getAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK_VIEW);
        try {
            $leadTime = $this->calculator->forStockItem($item, $request->get('orderQty'));
            return View::create(new LeadTimeSummary($leadTime));
        } catch (PurchasingDataException $ex) {
            return View::create(null, Response::HTTP_NO_CONTENT);
        }
    }
}
