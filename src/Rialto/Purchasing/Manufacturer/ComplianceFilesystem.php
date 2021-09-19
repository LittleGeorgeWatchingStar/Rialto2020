<?php

namespace Rialto\Purchasing\Manufacturer;

/**
 * Filesystem for storing records related to various kinds of compliance:
 * environmental, conflict-free, etc.
 */
final class ComplianceFilesystem extends ManufacturerFilesystem
{
    /**
     * Saves the file that was uploaded to $manuf that documents their
     * conflict-free status.
     */
    public function saveConflictFile(Manufacturer $manufacturer)
    {
        assertion($manufacturer->getId(), "$manufacturer has no ID");
        $file = $manufacturer->getConflictFile();
        if ($file === null) {
            return;
        }
        if ($manufacturer->getConflictFilename()) {
            $this->storage->delete($this->getKey($manufacturer));
        }
        $ext = $file->guessExtension() ?: $file->getClientOriginalExtension();
        $newName = "conflict_status.$ext";
        $manufacturer->setConflictFilename($newName);
        $key = $this->getKey($manufacturer);
        $this->storage->putFile($key, $file);
    }

    protected function getKey(Manufacturer $manufacturer): string
    {
        $dir = $this->getDirectory($manufacturer);
        $filename = $manufacturer->getConflictFilename();
        return "$dir/$filename";
    }
}
