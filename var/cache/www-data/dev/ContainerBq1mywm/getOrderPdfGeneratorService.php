<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Purchasing\Order\OrderPdfGenerator' shared autowired service.

return $this->services['Rialto\\Purchasing\\Order\\OrderPdfGenerator'] = new \Rialto\Purchasing\Order\OrderPdfGenerator(${($_ = isset($this->services['Rialto\\Filetype\\Pdf\\PdfGenerator']) ? $this->services['Rialto\\Filetype\\Pdf\\PdfGenerator'] : $this->load('getPdfGeneratorService.php')) && false ?: '_'});
