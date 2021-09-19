<?php

namespace Rialto\Purchasing\Order;

use Gumstix\Storage\File;
use Gumstix\Storage\FileStorage;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Item\Version\VersionIsSpecified;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Saves the build/engineering data files for a product to the filesystem.
 */
abstract class POBuildFiles
{
    /**
     * @var PurchaseOrder
     */
    protected $purchaseOrder;

    /**
     * The filesystem on which the files are actually stored.
     * @var FileStorage
     */
    private $storage;

    /**
     * @var UploadedFile[]
     * @Assert\Count(min=1, minMessage="No files uploaded.")
     * @Assert\All({
     *   @Assert\File(maxSize = "20M")
     * })
     */
    protected $uploaded = [];

    /**
     * Create a new BuildFiles instance for the given po.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return POBuildFiles
     */
    public static function create(
        PurchaseOrder $purchaseOrder,
        FileStorage $storage)
    {
        return new PurchasingOrderBuildFiles($purchaseOrder, $storage);
    }

    protected function __construct(
        PurchaseOrder $purchaseOrder,
        FileStorage $storage)
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->storage = $storage;
    }

    public function getPurchaseOrder()
    {
        return $this->purchaseOrder;
    }

    public function getPOId()
    {
        return $this->purchaseOrder->getId();
    }

    /** @return UploadedFile|null */
    protected function getUploaded(string $name)
    {
        return isset($this->uploaded[$name]) ? $this->uploaded[$name] : null;
    }

    /** @return string[] */
    public abstract function getSupportedFilenames();

    public function saveFiles()
    {
        foreach ($this->uploaded as $name => $fileobj) {
            if ($fileobj != null) {
                $this->replaceFile($name, $fileobj);
            }
        }
    }

    private function replaceFile(string $name, UploadedFile $fileobj)
    {
        /* Delete any existing files that match, in case the file extension
         * has changed. */
        $this->deleteFile($name);
        $this->saveFile($name, $fileobj);
    }

    private function saveFile(string $name, UploadedFile $fileobj)
    {
        $prefix = $this->getPrefix($name);
        $ext = $fileobj->guessExtension() ?: $fileobj->getClientOriginalExtension();
        $key = "$prefix.$ext";
        $this->storage->putFile($key, $fileobj);
    }

    private function deleteFile(string $name)
    {
        /* Delete all files matching the name, regardless of extension. */
        foreach ($this->listFiles($name) as $key) {
            $this->storage->delete($key);
        }
    }

    /** @return string[] */
    private function listFiles(string $name): array
    {
        $dirpath = $this->getPrefix($name);
        return $this->storage->listKeys($dirpath);
    }

    private function getPrefix(string $name): string
    {
        return $this->join(
            'purchase_order_build_files',
            $this->getPOId(),
            $name
        );
    }

    private function join(string ...$parts): string
    {
        return join('/', $parts);
    }

    /**
     * @param string $name eg "imageTop"
     * @return bool true if $name has been uploaded for this item
     */
    public function exists(string $name): bool
    {
        // We check for existence in this odd way because we don't know
        // the exact key -- the file extension is appended automatically.
        $keys = $this->listFiles($name);
        return count($keys) > 0;
    }

    /**
     * @param string $name eg "imageTop"
     */
    public function getFile(string $name): File
    {
        $key = $this->exists($name)
            ? $this->getFilepath($name)
            : $name;
        return $this->storage->getFile($key);
    }

    /**
     * @param string $name eg "imageTop"
     * @return string the file contents
     */
    public function getContents(string $name): string
    {
        $key = $this->getFilepath($name);
        return $this->storage->get($key);
    }

    /**
     * @param string $name eg "imageTop"
     */
    public function getFilepath(string $name): string
    {
        $keys = $this->listFiles($name);
        assertion(count($keys) > 0);
        return reset($keys);
    }

    /**
     * @param string $name eg "imageTop"
     */
    public function getMimeType(string $name): string
    {
        $key = $this->getFilepath($name);
        return $this->storage->getMimeType($key);
    }

    /**
     * Gets the filename with the file extension from the build file identifier.
     *
     * @param string $name The identifier; eg "imageTop"
     * @return string The basename; eg "imageTop.jpg"
     */
    public function getBasename(string $name): string
    {
        return basename($this->getFilepath($name));
    }
}
