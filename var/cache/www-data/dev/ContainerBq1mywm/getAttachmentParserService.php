<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Purchasing\Invoice\Reader\Email\AttachmentParser' shared autowired service.

$a = new \Rialto\Purchasing\Invoice\Reader\Email\AttachmentLocator();

$b = ${($_ = isset($this->services['Rialto\\Purchasing\\Invoice\\SupplierInvoiceFilesystem']) ? $this->services['Rialto\\Purchasing\\Invoice\\SupplierInvoiceFilesystem'] : $this->getSupplierInvoiceFilesystemService()) && false ?: '_'};
$c = ${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'};

$a->registerStrategy(new \Rialto\Purchasing\Invoice\Reader\Email\AttachmentLocatorAttachment($b));
$a->registerStrategy(new \Rialto\Purchasing\Invoice\Reader\Email\AttachmentLocatorBodyLink(new \GuzzleHttp\Client(), $b));
$a->registerStrategy(new \Rialto\Purchasing\Invoice\Reader\Email\AttachmentLocatorUpsBodyLink($b, ${($_ = isset($this->services['Rialto\\Legacy\\CurlHelper']) ? $this->services['Rialto\\Legacy\\CurlHelper'] : $this->load('getCurlHelperService.php')) && false ?: '_'}, $c));
$d = ${($_ = isset($this->services['Rialto\\Filesystem\\TempFilesystem']) ? $this->services['Rialto\\Filesystem\\TempFilesystem'] : ($this->services['Rialto\\Filesystem\\TempFilesystem'] = new \Rialto\Filesystem\TempFilesystem())) && false ?: '_'};

return $this->services['Rialto\\Purchasing\\Invoice\\Reader\\Email\\AttachmentParser'] = new \Rialto\Purchasing\Invoice\Reader\Email\AttachmentParser($a, new \Rialto\Purchasing\Invoice\Reader\Email\AttachmentConverter(new \Rialto\Filetype\Pdf\PdfConverter($d), new \Rialto\Filetype\Image\OcrConverter($d), $b), new \Rialto\Purchasing\Invoice\Parser\SupplierInvoiceParser(${($_ = isset($this->services['JMS\\Serializer\\SerializerInterface']) ? $this->services['JMS\\Serializer\\SerializerInterface'] : $this->load('getSerializerInterfaceService.php')) && false ?: '_'}, $c));
