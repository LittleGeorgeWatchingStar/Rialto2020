<?php


namespace Gumstix\Storage;


/**
 * All types of file storage must implement this interface.
 */
interface FileStorage
{
    /**
     * @param string $key the file identifier
     * @return string the file contents
     * @throws StorageException
     */
    public function get($key);

    /** @return File */
    public function getFile($key);

    /**
     * @param string $key the file identifier
     * @param string $data the file contents
     * @throws StorageException
     */
    public function put($key, $data);

    /**
     * @param string $key the file identifier
     * @param \SplFileInfo $file the file whose contents will be saved
     * @throws StorageException
     */
    public function putFile($key, \SplFileInfo $file);

    /**
     * @param string $key
     * @return bool true if $key exists, false otherwise
     */
    public function exists($key);

    /**
     * Lists files whose names begin with $prefix.
     *
     * Does not list directories.
     *
     * @param string $prefix
     * @return string[]
     */
    public function listKeys($prefix = '');

    /**
     * Deletes the file if it exists.
     *
     * No-op if the file does not exist.
     *
     * @param string $key the file identifier
     */
    public function delete($key);

    /** @return string */
    public function getMimeType($key);
}
