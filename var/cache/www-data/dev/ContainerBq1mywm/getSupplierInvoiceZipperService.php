<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Purchasing\Invoice\SupplierInvoiceZipper' shared autowired service.

return $this->services['Rialto\\Purchasing\\Invoice\\SupplierInvoiceZipper'] = new \Rialto\Purchasing\Invoice\SupplierInvoiceZipper(${($_ = isset($this->services['Rialto\\Filesystem\\TempFilesystem']) ? $this->services['Rialto\\Filesystem\\TempFilesystem'] : ($this->services['Rialto\\Filesystem\\TempFilesystem'] = new \Rialto\Filesystem\TempFilesystem())) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Purchasing\\Invoice\\SupplierInvoiceFilesystem']) ? $this->services['Rialto\\Purchasing\\Invoice\\SupplierInvoiceFilesystem'] : $this->getSupplierInvoiceFilesystemService()) && false ?: '_'}, ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->getDoctrine_Orm_DefaultEntityManagerService()) && false ?: '_'});
