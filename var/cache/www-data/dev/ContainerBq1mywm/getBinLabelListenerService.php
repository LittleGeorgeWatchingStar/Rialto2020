<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Stock\Bin\Label\BinLabelListener' shared autowired service.

return $this->services['Rialto\\Stock\\Bin\\Label\\BinLabelListener'] = new \Rialto\Stock\Bin\Label\BinLabelListener(${($_ = isset($this->services['Rialto\\Stock\\Bin\\Label\\BinLabelPrintQueue']) ? $this->services['Rialto\\Stock\\Bin\\Label\\BinLabelPrintQueue'] : $this->load('getBinLabelPrintQueueService.php')) && false ?: '_'});
