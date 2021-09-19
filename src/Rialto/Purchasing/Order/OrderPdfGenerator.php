<?php

namespace Rialto\Purchasing\Order;

use Rialto\Filetype\Pdf\PdfGenerator;

/**
 * Generates purchase order PDF documents.
 */
class OrderPdfGenerator
{
    /** @var PdfGenerator */
    private $generator;

    public function __construct(PdfGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @return string PDF data
     */
    public function generatePdf(PurchaseOrder $order)
    {
        return $this->generator->render('purchasing/order/pdf.tex.twig', [
            'order' => $order,
        ]);
    }

}
