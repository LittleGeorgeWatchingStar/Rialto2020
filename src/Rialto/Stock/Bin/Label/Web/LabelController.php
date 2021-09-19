<?php

namespace Rialto\Stock\Bin\Label\Web;

use Rialto\Accounting\Transaction\SystemType;
use Rialto\Manufacturing\WorkType\ProductLabelPrinter;
use Rialto\Printing\Printer\LabelPrinter;
use Rialto\Printing\Printer\PrinterException;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Security\Role\Role;
use Rialto\Stock\Bin\Label\BinLabel;
use Rialto\Stock\Bin\Label\BinLabelPrintQueue;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Bin\StockBinVoter;
use Rialto\Stock\Item;
use Rialto\Stock\Item\Document\ProductLabel;
use Rialto\Stock\Item\StockItem;
use Rialto\Supplier\Web\SupplierController;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\Response\PdfResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for printing labels.
 */
class LabelController extends SupplierController
{

    /**
     * @Route("/Stock/BinLabels/{id}",
     *  name="Stock_BinLabels",
     *  defaults={"_format" = "json"})
     * @Method("POST")
     */
    public function printBinLabelAction(StockBin $bin)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::WAREHOUSE]);
        /** @var $printer LabelPrinter */
        $printer = LabelPrinter::get('label', $this->dbm);
        $label = new BinLabel($bin);
        try {
            $printer->printLabel($label, $label->getNumCopies());
        } catch (PrinterException $ex) {
            return JsonResponse::fromException($ex, Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return JsonResponse::fromMessages([
            "Labels printed successfully."
        ]);
    }

    /**
     * @Route("/Stock/BinLabels/{id}/pdf",
     *  name="Stock_BinLabels_pdf")
     * @Method("POST")
     */
    public function downloadBinLabelAction(StockBin $bin)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::SUPPLIER_SIMPLE]);
        $label = new BinLabel($bin);
        return PdfResponse::create($label->render(), "binLabel");
    }

    /**
     * @Route("/supplier/Stock/BinLabels/{id}/pdf",
     *  name="supplier_Stock_BinLabels_pdf")
     * @Method("POST")
     */
    public function suppliersDownloadBinLabelAction(StockBin $bin,
                                                    BinLabelPrintQueue $queue)
    {
        $this->denyAccessUnlessGranted(StockBinVoter::VIEW, $bin);
        $pdf = $queue->renderPdfLabel($bin);
        $filename = "{$bin->getId()}-{$bin->getFullSku()}-label.pdf";
        return PdfResponse::create($pdf, $filename);
    }

    /**
     * @Route("/Stock/BinLabelsForMove/{type}/{typeNo}",
     *  name="Stock_BinLabelsForMove")
     * @Method("GET")
     * @Template("stock/label/list.html.twig")
     */
    public function listBinsForMoveAction(SystemType $type, $typeNo)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::WAREHOUSE]);
        /** @var $repo StockBinRepository */
        $repo = $this->getRepository(StockBin::class);
        $bins = $repo->findBySystemType($type, $typeNo);

        return [
            'bins' => $bins,
            'type' => $type,
            'typeNo' => $typeNo,
        ];
    }

    /**
     * Print a product label for a bin.
     *
     * @Route("/stock/item/{item}/version/{version}/product-label/",
     *   name="stock_item_product_label",
     *   defaults={"version" = ""})
     * @Method("POST")
     */
    public function productLabelAction(StockItem $item, $version,
                                       ProductLabelPrinter $printer)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $version = $item->getVersion($version);
        $label = new ProductLabel($version, 1);
        try {
            $printer->printLabels($label);
            $this->dbm->flush();
        } catch (PrinterException $ex) {
            $this->logException($ex);
            return $this->redirectToItem($item);
        }

        $this->logNotice(sprintf("Label for %s printed successfully.", $version->getFullSku()));
        return $this->redirectToItem($item);
    }

    /**
     *
     * @Route("/supplier/po/{id}/product-label/", name="stock_item_pdf_product_label")
     * @Template("stock/label/productLabel.html.twig")
     * @Method("GET")
     */
    public function productLabelPageAction(PurchaseOrder $po)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_SIMPLE, Role::EMPLOYEE]);
        $supplier = $po->getSupplier();

        return [
            'cancelUri' => $this->getDashboardUri($supplier),
            'po' => $po,
        ];
    }

    /**
     * @Route("/supplier/po-item/{id}/pdf/product-label", name="pdf_product_label")
     * @Method("GET")
     */
    public function downloadProductLabelAction(StockProducer $item,
                                               ProductLabelPrinter $printer)
    {
        $this->denyAccessUnlessGranted( Role::SUPPLIER_SIMPLE);
        $stockItem = $item->getStockItem();
        if ($stockItem !== null) {
            $version = $item->getVersion();
            $itemVersion = $item->getStockItem()->getVersion($version);
            $label = new ProductLabel($itemVersion, 1);
            $pdf = $printer->renderPdfLabel($label);
            $filename = "{$itemVersion->getFullSku()}-label.pdf";
            return PdfResponse::create($pdf, $filename);
        }
        throw $this->badRequest("No Stock Item for PO entry '{$item->getId()}'");
    }

    private function redirectToItem(Item $item)
    {
        return $this->redirectToRoute('stock_item_view', [
            'item' => $item->getSku(),
        ]);
    }
}
