<?php

namespace Rialto\Stock\Bin\Label;

use Rialto\Port\FormatConversion\PostScriptToPdfConverter;
use Rialto\Printing\Job\PrintJob;
use Rialto\Printing\Job\PrintQueue;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Location;

/**
 * Queues up bin labels to be printed.
 */
class BinLabelPrintQueue
{
    /**
     * The service ID of the printer that should print bin labels.
     */
    const PRINTER_ID = 'label';

    /** @var PrintQueue */
    private $printQueue;

    /** @var PostScriptToPdfConverter */
    private $pdfConverter;

    public function __construct(PrintQueue $queue,
                                PostScriptToPdfConverter $pdfConverter)
    {
        $this->printQueue = $queue;
        $this->pdfConverter = $pdfConverter;
    }

    public function renderPdfLabel(StockBin $bin): string
    {
        $label = new BinLabel($bin);
        return $this->pdfConverter->toPdf($label->render());
    }

    /**
     * @param StockBin[] $bins
     */
    public function printLabels(array $bins)
    {
        foreach ($bins as $bin) {
            $this->printLabelIfNeeded($bin);
        }
    }

    private function printLabelIfNeeded(StockBin $bin)
    {
        if ($bin->getQuantity() <= 0) {
            return;
        }
        if (! $this->needsLabels($bin->getLocation())) {
            return;
        }
        if ($bin->getNumLabels() <= 0) {
            return;
        }

        $label = new BinLabel($bin);
        $job = PrintJob::postscript($label->render());
        $job->setDescription("label for $bin");
        $this->printQueue->add($job, self::PRINTER_ID);
    }

    private function needsLabels(Location $location)
    {
        if (!$location instanceof Facility) {
            return false;
        }
        return $location->isHeadquarters() ||
            $location->isProductTesting();
    }
}
