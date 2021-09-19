<?php


namespace Rialto\Purchasing\Manufacturer;


use Gumstix\Storage\FileStorage;

/**
 * Base Filesystem for storing data related to a Manufacturer.
 */
abstract class ManufacturerFilesystem
{
    /** @var FileStorage */
    protected $storage;

    public function __construct(FileStorage $storage)
    {
        $this->storage = $storage;
    }

    abstract protected function getKey(Manufacturer $manufacturer): string;

    protected function getDirectory(Manufacturer $manufacturer)
    {
        return join('/', ['compliance', 'manufacturer', $manufacturer->getId()]);
    }

    public function getFileContents(Manufacturer $manufacturer): string
    {
        return $this->storage->get($this->getKey($manufacturer));
    }
}