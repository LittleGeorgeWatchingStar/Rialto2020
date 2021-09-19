<?php

namespace Rialto\Stock\Web;

use Gumstix\Filetype\CsvFileWithHeadings;
use Rialto\Filetype\Pdf\PdfGenerator;
use Rialto\Security\Role\Role;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Cost\InventoryValuation;
use Rialto\Stock\Facility\Web\AllStockReport;
use Rialto\Stock\Facility\Web\AllStockReportType;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\Response\PdfResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for generating various reports about inventory.
 */
class InventoryReportController extends RialtoController
{
    /**
     * @Route("/Stock/InventoryValuation", name="Stock_InventoryValuation")
     */
    public function valuationAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $valuation = new InventoryValuation($this->dbm);
        $form = $this->createForm(InventoryValuationType::class, $valuation);

        $byCategory = [];
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $byCategory = $valuation->getCategoryValuations();
        }
        $byAccount = $valuation->getAccountTotals($byCategory);

        if (count($byCategory) && $request->get('_format') == 'pdf') {
            $template = $request->get('summarize') ?
                'stock/inventory-report/summary.tex.twig' :
                'stock/inventory-report/valuation.tex.twig';
            $tex = $this->renderView($template, [
                'byCategory' => $byCategory,
                'byAccount' => $byAccount,
                'date' => $valuation->getDate(),
            ]);
            $generator = $this->get(PdfGenerator::class);
            $pdf = $generator->fromTex($tex);
            return PdfResponse::create($pdf, 'inventory_valuation.pdf');
        }
        if (count($byCategory) && $request->get('_format') == 'csv') {
            $rows = [];
            foreach ($byCategory as $category) {
                $categoryName = $category->getDescription();
                foreach ($category->getItems() as $item) {
                    $rows[] = [
                        'Item' => $item->getSku(),
                        'Category' => $categoryName,
                        'Description' => $item->getDescription(),
                        'Quantity' => $item->getQuantity(),
                        'Unit cost' => $item->getStandardCost(),
                        'Cost Last Updated' => $item->getLastUpdated() ? $item->getLastUpdated()->format('m-d-Y') : 'None',
                        'Last PO Invoice Unit Cost' => $item->getMostRecentPoUnitCost() ?: 'None',
                        'Estimated?' => ($item->getMostRecentPoUnitCost() && !$item->usingActualPoUnitCost()) ? '*' : '',
                        'Last PO Date' => $item->getMostRecentPoDate() ? $item->getMostRecentPoDate()->format('m-d-Y') : 'None',
                        'Total value' => $item->getTotalValue(),
                    ];
                }
            }
            $csv = new CsvFileWithHeadings();
            $csv->parseArray($rows);
            return FileResponse::fromData($csv->toString(), 'inventory_valuation.csv', 'text/csv');

        }

        $template = $request->get('summarize') ?
            'stock/inventory-report/summary.html.twig' :
            'stock/inventory-report/valuation.html.twig';
        return $this->render($template, [
            'form' => $form->createView(),
            'byCategory' => $byCategory,
            'byAccount' => $byAccount,
            'date' => $valuation->getDate(),
        ]);
    }

    /**
     * @Route("/Stock/AllStock", name="Stock_AllStock")
     * @Template("core/form/newForm.html.twig")
     */
    public function allStockAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $report = new AllStockReport();
        $form = $this->createForm(AllStockReportType::class, $report);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $repo StockBinRepository */
            $repo = $this->getRepository(StockBin::class);
            $bins = $repo->findForStockReport($report);
            $bins = StockBin::indexByStockCode($bins);

            $generator = $this->get(PdfGenerator::class);
            $pdf = $generator->render('stock/inventory-report/pdf.tex.twig', [
                'bins' => $bins,
                'title' => $report->getTitle(),
            ]);
            return FileResponse::fromData($pdf, 'allstock.pdf', 'application/pdf');
        }

        return [
            'form' => $form->createView(),
            'heading' => 'Show all stock',
            'submitLabel' => 'Download PDF',
        ];
    }
}
