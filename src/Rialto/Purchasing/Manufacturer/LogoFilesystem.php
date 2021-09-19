<?php


namespace Rialto\Purchasing\Manufacturer;


/**
 * Filesystem for storing manufacturer logos.
 */
final class LogoFilesystem extends ManufacturerFilesystem
{
    public function saveLogoFile(Manufacturer $manufacturer)
    {
        assertion($manufacturer->getId(), "$manufacturer has no ID");

        $file = $manufacturer->getLogoFile();
        if ($file === null) return;

        if ($manufacturer->getLogoFilename()) {
            $this->storage->delete($this->getKey($manufacturer));
        }

        $ext = $file->guessExtension() ?: $file->getClientOriginalExtension();
        $newName = "logo.$ext";
        $manufacturer->setLogoFilename($newName);

        $key = $this->getKey($manufacturer);
        $this->storage->putFile($key, $file);
    }

    protected function getKey(Manufacturer $manufacturer): string
    {
        $dir = $this->getDirectory($manufacturer);
        $filename = $manufacturer->getLogoFilename();
        return "$dir/$filename";
    }
}