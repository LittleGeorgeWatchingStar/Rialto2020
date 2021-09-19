<?php

namespace Rialto\Manufacturing\WorkOrder\Filesystem;

use Gumstix\Storage\FileStorage;
use Rialto\Stock\Item\Version\Version;

/**
 * Stores flash images (eg, for memory cards) and flashing instructions.
 */
class FlashFilesystem
{
    /** @var FileStorage */
    private $storage;

    public function __construct(FileStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return string|null
     */
    public function getInstructions(Version $version, $filename="INSTRUCTIONS.txt")
    {
        $lookFor = "$version/$filename";
        foreach ($this->storage->listKeys('flash_images') as $key) {
            if (is_substring($lookFor, $key)) {
                return $this->storage->get($key);
            }
        }
        return null;
    }

}
