<?php

namespace Rialto\Panelization;

use Gumstix\Storage\FileStorage;
use Rialto\Panelization\IO\PanelizationStorage;
use Rialto\Panelization\Web\PanelPdfGenerator;

/**
 * Generates and stores data files (assets) for panelized orders, such
 * as consolidated BOMs, XY data and layout PDFs.
 *
 * @see ConsolidatedBom
 * @see ConsolidatedXY
 * @see PanelPdfGenerator
 */
class AssetManager
{
    /** @var FileStorage */
    private $storage;

    /** @var PanelPdfGenerator */
    private $pdfGenerator;

    public function __construct(FileStorage $storage, PanelPdfGenerator $pdfGenerator)
    {
        $this->storage = $storage;
        $this->pdfGenerator = $pdfGenerator;
    }

    public function generateAndStoreAssets(Panel $panel)
    {
        $po = $panel->getPurchaseOrder();
        $bom = $panel->generateConsolidatedBom();
        $xy = $panel->generateConsolidatedXY($this->storage);
        $layout = $this->pdfGenerator->generateLayout($panel, $xy);

        $panelStorage = new PanelizationStorage($this->storage);
        $panelStorage->storeConsolidatedBom($po, $bom);
        $panelStorage->storeConsolidatedXy($po, $xy);
        $panelStorage->storeLayoutPdf($po, $layout);
    }
}
