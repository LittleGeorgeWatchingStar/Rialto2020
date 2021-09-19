<?php

namespace Rialto\Filetype;

use Rialto\Filesystem\FilesystemException;
use SplFileInfo;
use ZipArchive;


/**
 * An exception-throwing wrapper around PHP's built-in \ZipArchive class.
 */
class ZipFile
{
    /** @var ZipArchive */
    private $zip;

    /**
     * @param SplFileInfo|string $filepath
     * @throws FilesystemException
     */
    public function open($filepath)
    {
        $this->close();
        $this->zip = new ZipArchive();
        if ($this->zip->open((string) $filepath) !== true) {
            throw new FilesystemException($filepath, "Unable to open");
        }
    }

    /**
     * @param SplFileInfo|string $destination
     * @throws FilesystemException
     */
    public function extractTo($destination)
    {
        if (!$this->zip->extractTo((string) $destination)) {
            throw new FilesystemException($destination, "Unable to extract zip archive");
        }
    }

    public function close()
    {
        if ($this->zip) {
            $this->zip->close();
            $this->zip = null;
        }
    }
}
