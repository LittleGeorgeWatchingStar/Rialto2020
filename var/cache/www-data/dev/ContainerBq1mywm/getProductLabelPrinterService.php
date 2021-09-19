<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Manufacturing\WorkType\ProductLabelPrinter' shared autowired service.

return $this->services['Rialto\\Manufacturing\\WorkType\\ProductLabelPrinter'] = new \Rialto\Manufacturing\WorkType\ProductLabelPrinter('product', ${($_ = isset($this->services['Rialto\\Printing\\Job\\PrintQueue']) ? $this->services['Rialto\\Printing\\Job\\PrintQueue'] : $this->load('getPrintQueueService.php')) && false ?: '_'}, ${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Allocation\\Allocation\\AllocationFactory']) ? $this->services['Rialto\\Allocation\\Allocation\\AllocationFactory'] : ($this->services['Rialto\\Allocation\\Allocation\\AllocationFactory'] = new \Rialto\Allocation\Allocation\AllocationFactory())) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Manufacturing\\WorkOrder\\Issue\\WorkOrderIssuer']) ? $this->services['Rialto\\Manufacturing\\WorkOrder\\Issue\\WorkOrderIssuer'] : $this->load('getWorkOrderIssuerService.php')) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Purchasing\\Receiving\\Receiver']) ? $this->services['Rialto\\Purchasing\\Receiving\\Receiver'] : $this->load('getReceiverService.php')) && false ?: '_'}, ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : ($this->services['security.token_storage'] = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage())) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Port\\FormatConversion\\PostScriptToPdfConverter']) ? $this->services['Rialto\\Port\\FormatConversion\\PostScriptToPdfConverter'] : ($this->services['Rialto\\Port\\FormatConversion\\PostScriptToPdfConverter'] = new \Infrastructure\FormatConversion\GhostscriptConverter())) && false ?: '_'});