<?php

namespace Rialto\Filesystem;


/**
 * Filesystem for dealing with temporary files.
 */
class TempFilesystem extends Filesystem
{
    public function __construct($rootDir = null)
    {
        $rootDir = $rootDir ?: sys_get_temp_dir();
        parent::__construct($rootDir);
    }

    /**
     * Returns the path of a new temporary file.
     * @return string
     */
    public function getTempfile($prefix, $ext = 'tmp')
    {
        $name = uniqid($prefix);
        $fileinfo = $this->join(
            $this->rootDir,
            "$name.$ext");
        return $fileinfo->getPathname();
    }
}
