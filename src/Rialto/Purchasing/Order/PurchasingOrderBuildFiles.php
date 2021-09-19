<?php

namespace Rialto\Purchasing\Order;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Saves the build/engineering data files for a PCB to the filesystem.
 */
class PurchasingOrderBuildFiles extends POBuildFiles
{
    const PANELIZED = 'panelizedGerbers';

    public function getSupportedFilenames()
    {
        return [
            self::PANELIZED,
        ];
    }

    /** @return File|null */
    public function getPanelizedGerbers()
    {
        return $this->getUploaded(self::PANELIZED);
    }

    public function setPanelizedGerbers(UploadedFile $file = null)
    {
        $this->uploaded[self::PANELIZED] = $file;
    }
}
