<?php

namespace Rialto\Stock\Consumption\Web;

use Rialto\Exception\InvalidDataException;
use Rialto\Filetype\Pdf\PdfGenerator;
use Rialto\Security\Role\Role;
use Rialto\Stock\Consumption\StockConsumptionReport;
use Rialto\Stock\Item\Version\ItemVersionException;
use Rialto\Stock\Item\Version\VersionException;
use Rialto\Stock\Level\StockLevelService;
use Rialto\Web\Response\PdfResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Twig\Error\RuntimeError;

/**
 * Stock consumption shows the rate at which product sales are using up
 * purchased components that make up those products.
 */
class StockConsumptionController extends RialtoController
{
    /**
     * @Route("/Stock/StockConsumption/", name="Stock_StockConsumption")
     * @Template("stock/consumption/report.html.twig")
     */
    public function reportAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $stats = [];
        /** @var $stockLevels StockLevelService */
        $stockLevels = $this->get(StockLevelService::class);
        $report = new StockConsumptionReport($this->dbm, $stockLevels);
        $form = $this->createForm(StockConsumptionReportType::class, $report);

        $action = $request->get("action");
        if ($action) {
            $form->submit($request->query->all());
            if ($form->isValid()) {
                try {
                    $stats = $report->getStatistics();
                } catch (ItemVersionException $ex) {
                    $this->logException($ex);
                    return $this->redirectToRoute('item_version_edit', [
                        'item' => $ex->getSku(),
                        'version' => $ex->getVersionCode(),
                    ]);
                } catch (VersionException $ex) {
                    $this->logException($ex);
                    return $this->redirectToRoute('stock_item_view', [
                        'item' => $ex->getSku(),
                    ]);
                }
                switch ($action) {
                    case "PDF":
                        return $this->renderPdf($report, $stats);
                }
            }
        }

        try {
            return [
                'components' => $stats,
                'form' => $form->createView(),
                'report' => $report,
                'stats' => $stats,
            ];
        } catch (RuntimeError $ex) {
            return $this->handleTwigError($ex);
        }
    }

    private function renderPdf(StockConsumptionReport $report, array $stats)
    {
        /* @var $generator PdfGenerator */
        $generator = $this->get(PdfGenerator::class);
        try {
            $pdf = $generator->render(
                'stock/consumption/pdf.tex.twig', [
                'report' => $report,
                'stats' => $stats,
            ]);
        } catch (RuntimeError $ex) {
            return $this->handleTwigError($ex);
        }
        return PdfResponse::create($pdf, 'stock_consumption.pdf');
    }

    private function handleTwigError(RuntimeError $ex)
    {
        $previous = $ex->getPrevious();
        if ($previous instanceof InvalidDataException) {
            $this->logException($previous);
            $url = $this->generateUrl('Stock_StockConsumption');
            return $this->redirect($url);
        }
        throw $ex;
    }
}
