<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Filing\DocumentFilesystem' shared autowired service.

return $this->services['Rialto\\Filing\\DocumentFilesystem'] = new \Rialto\Filing\DocumentFilesystem(${($_ = isset($this->services['Gumstix\\Storage\\FileStorage']) ? $this->services['Gumstix\\Storage\\FileStorage'] : $this->getFileStorageService()) && false ?: '_'});
