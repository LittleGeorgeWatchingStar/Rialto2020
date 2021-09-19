<?php

namespace Rialto\Filesystem;

use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Base class for filesystems.
 */
class Filesystem extends SymfonyFilesystem
{
    /** @var SplFileInfo */
    protected $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = new SplFileInfo(realpath($rootDir));
        if (!$this->rootDir->isDir()) {
            throw new \InvalidArgumentException("No such directory $rootDir");
        }
    }

    /**
     * Joins the arguments with path separators and returns an
     * SplFileInfo object for the corresponding file.
     */
    public function join(string ...$parts): SplFileInfo
    {
        return new SplFileInfo(join(DIRECTORY_SEPARATOR, $parts));
    }

    /**
     * Joins the arguments to the root directory with path separators
     * and returns an SplFileInfo object for the corresponding file.
     */
    public function append(string ...$parts): SplFileInfo
    {
        array_unshift($parts, $this->rootDir);
        return new SplFileInfo(join(DIRECTORY_SEPARATOR, $parts));
    }

    /**
     * Returns the entire contents of the given file.
     *
     * @throws FilesystemException
     *  If the file could not be read.
     */
    public function getContents(SplFileInfo $fileinfo): string
    {
        if (!$fileinfo->isReadable()) {
            throw new FilesystemException($fileinfo, 'not readable');
        }
        $result = file_get_contents($fileinfo->getRealPath());
        if (false === $result) {
            throw new FilesystemException($fileinfo, 'unable to read');
        }
        return $result;
    }

    /**
     * @param SplFileInfo|string $dirname
     *  The directory path
     * @param integer $mode 0775 by default
     * @throws FilesystemException
     *  if the directory could not be created.
     */
    public function mkdir($dirname, $mode = 0775)
    {
        $dirname = (string) $dirname;
        parent::mkdir($dirname, $mode);
        if (!is_dir($dirname)) {
            throw new FilesystemException($dirname, "Unable to create directory");
        }
    }

    /** @return File */
    protected function tempFile($dir, $prefix)
    {
        $filename = tempnam((string) $dir, $prefix);
        if (false === $filename) {
            throw new FilesystemException($dir, "Unable to create temporary file");
        }
        return new File($filename);
    }

    /**
     * @param SplFileInfo $filepath
     * @param mixed $data
     * @return int The number of bytes written.
     * @throws FilesystemException
     *  If the write cannot be written.
     */
    protected function write(SplFileInfo $filepath, $data): int
    {
        $this->touch($filepath);
        if (!$filepath->isWritable()) {
            throw new FilesystemException($filepath, "not writable");
        }
        $numBytes = file_put_contents($filepath->getRealPath(), $data);
        if (false === $numBytes) {
            throw new FilesystemException($filepath, 'unable to write');
        }
        return $numBytes;
    }

    /**
     * @param SplFileInfo $dir
     *  The directory to list. Defaults to the root directory.
     * @return SplFileInfo[]
     */
    public function ls(SplFileInfo $dir = null): array
    {
        if (null === $dir) {
            $dir = $this->rootDir;
        }
        if (!$dir->isDir()) {
            throw new \InvalidArgumentException("$dir is not a directory");
        }

        $files = [];
        $dirh = opendir($dir->getRealPath());
        while (false !== ($entry = readdir($dirh))) {
            $files[] = $this->join($dir, $entry);
        }
        return $files;
    }

    /**
     * @param SplFileInfo $dir
     *  The directory to list. Defaults to the root directory.
     * @return File[]
     *  All files in $dir that are not hidden files or directories.
     */
    public function lsRegularFiles(SplFileInfo $dir = null)
    {
        $files = $this->ls($dir);
        $regular = array_filter($files, function (SplFileInfo $file) {
            if (strpos($file->getFilename(), '.') === 0) {
                return false;
            }
            return !$file->isDir();
        });

        /* Convert SplFileInfo to more useful subclass File. */
        return array_map(function (SplFileInfo $file) {
            return new File((string) $file);
        }, $regular);
    }
}

