<?php

namespace Rialto\Filesystem;

use Rialto\ResourceException;

/**
 * Thrown when an error occurs accessing a filesystem.
 */
class FilesystemException extends ResourceException
{
    /**
     *
     * @param string|\SplFileInfo $path
     *  The file path that generated the exception.
     * @param string $message (optional)
     */
    public function __construct($path, $message = null)
    {
        if ($path instanceof \SplFileInfo) {
            $path = $path->getPathname();
        }

        $fullMsg = "Error accessing file at $path";
        if ($message) $fullMsg .= ": $message";
        parent::__construct($fullMsg);
    }
}
