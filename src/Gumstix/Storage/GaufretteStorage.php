<?php

namespace Gumstix\Storage;

use Aws\S3\S3Client;
use Gaufrette\Adapter;
use Gaufrette\Filesystem;

/**
 * A Storage implementation that uses a Gaufrette filesystem backend.
 *
 * @see https://github.com/KnpLabs/Gaufrette
 */
class GaufretteStorage implements FileStorage
{
    /** @var Filesystem */
    private $fs;

    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    /**
     * Factory method to create an in-memory file storage.
     *
     * Useful for testing.
     *
     * @param array $files
     */
    public static function memory(array $files = []): self
    {
        return self::fromAdapter(new Adapter\InMemory($files));
    }

    /**
     * Factory method to create a local filesystem storage.
     */
    public static function local(string $filepath): self
    {
        $adapter = new Adapter\Local($filepath, $create = true, $mode = 0750);
        return self::fromAdapter($adapter);
    }

    /**
     * Factory method to create a zip file.
     *
     * @param string $filepath The path to the zip file.
     */
    public static function zipfile(string $filepath): self
    {
        $adapter = new Adapter\Zip($filepath);
        return self::fromAdapter($adapter);
    }

    /**
     * Factory method to create an AWS S3 filesystem storage.
     */
    public static function awsS3(S3Client $client, $bucketName): self
    {
        $options = [];
        $detectMimeType = true;
        $adapter = new Adapter\AwsS3($client, $bucketName, $options, $detectMimeType);
        return self::fromAdapter($adapter);
    }

    private static function fromAdapter(Adapter $adapter): self
    {
        $fs = new Filesystem($adapter);
        return new self($fs);
    }

    /**
     * @param string $key the file identifier
     * @return string the file contents
     * @throws StorageException
     */
    public function get($key)
    {
        try {
            return $this->fs->read($key);
        } catch (\RuntimeException $ex) {
            throw StorageException::fromPrevious($ex);
        }
    }

    /** @return File */
    public function getFile($key)
    {
        return new GaufretteFile($this->fs->createFile($key));
    }

    /**
     * @param string $key the file identifier
     * @param string $data the file contents
     * @throws StorageException
     */
    public function put($key, $data)
    {
        try{
            $this->fs->write($key, $data, $overwrite = true);
        } catch (\RuntimeException $ex) {
            throw StorageException::fromPrevious($ex);
        }
    }

    /**
     * @param string $key the file identifier
     * @param \SplFileInfo $file the file whose contents will be saved
     * @throws StorageException
     */
    public function putFile($key, \SplFileInfo $file)
    {
        $this->validateFile($file);
        $path = $file->getRealPath();
        $data = file_get_contents($path);
        if (false === $data) {
            throw new StorageException("Unable to read from $path");
        }
        $this->put($key, $data);
    }

    private function validateFile(\SplFileInfo $file)
    {
        if (! $file->isReadable()) {
            $filename = $file->getFilename();
            throw new StorageException("Unable to read $filename; does it exist?");
        }
    }


    /**
     * @param string $key
     * @return bool true if $key exists, false otherwise
     */
    public function exists($key)
    {
        return $this->fs->has($key);
    }

    /**
     * Deletes the file if it exists.
     *
     * @param string $key the file identifier
     */
    public function delete($key)
    {
        try{
            if ($this->fs->has($key)) {
                $this->fs->delete($key);
            }
        } catch (\RuntimeException $ex) {
            throw StorageException::fromPrevious($ex);
        }
    }

    /**
     * Returns all regular filenames beginning with $prefix.
     *
     * Symlinks are included; directories are not.
     *
     * @param string $prefix
     * @return string[]
     */
    public function listKeys($prefix = '')
    {
        try {
            $results = $this->fs->listKeys($prefix);
            /* Different adapters return different data structures; see
             * https://github.com/KnpLabs/Gaufrette/issues/344 */
            return isset($results['keys'])
                ? $results['keys']
                : $results;
        } catch (\RuntimeException $ex) {
            throw StorageException::fromPrevious($ex);
        }
    }

    /** @return string */
    public function getMimeType($key)
    {
        try {
            return $this->fs->mimeType($key);
        } catch (\LogicException $ex) {
            return ContentType::fromData($this->get($key));
        } catch (\RuntimeException $ex) {
            throw StorageException::fromPrevious($ex);
        }
    }
}
