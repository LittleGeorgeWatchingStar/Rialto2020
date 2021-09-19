<?php

namespace Rialto\Panelization\Web;

use Rialto\Filetype\Pdf\PdfGenerator;
use Rialto\Panelization\ConsolidatedXY;
use Rialto\Panelization\Panel;


/**
 * Generates the layout PDF for a Panel.
 */
class PanelPdfGenerator
{
    const OFFSET = 10.0;

    /** @var PdfGenerator */
    private $pdfGenerator;

    public function __construct(PdfGenerator $pdfGenerator)
    {
        $this->pdfGenerator = $pdfGenerator;
    }

    public function generateLayout(Panel $panel, ConsolidatedXY $xy)
    {
        $template = "panelization/panelization/layout.tex.twig";
        return $this->pdfGenerator->render($template, [
            'panel' => $panel,
            'xy' => $xy->toArray(),
            'offset' => self::OFFSET,
        ]);
    }
}
