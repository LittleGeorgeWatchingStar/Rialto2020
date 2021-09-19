<?php

namespace Rialto\Util\Lock;

use Rialto\Filesystem\FilesystemException;

/**
 * A semaphore implementation that uses a file lock.
 */
class FileSemaphore
implements Semaphore
{
    private $filename;
    private $filehandle = null;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function acquire()
    {
        if (! is_file($this->filename) ) {
            $this->createLockFile();
        }
        $this->filehandle = fopen($this->filename, 'w+');
        if (! $this->filehandle ) throw new FilesystemException(
            $this->filename,
            "unable to open for locking");
        return flock($this->filehandle, LOCK_EX | LOCK_NB);
    }

    private function createLockFile()
    {
        if (! touch($this->filename) ) {
            throw new FilesystemException($this->filename, "unable to create");
        }
        assert( is_file($this->filename));
    }

    public function release()
    {
        if (! flock($this->filehandle, LOCK_UN) ) {
            throw new FilesystemException(
                $this->filename,
                "unable to release lock");
        }
        if (! fclose($this->filehandle) ) {
            throw new FilesystemException(
                $this->filename,
                "unable to close filehandle");
        }
        $this->filehandle = null;
    }


}
