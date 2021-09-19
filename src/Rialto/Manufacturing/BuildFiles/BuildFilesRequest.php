<?php

namespace Rialto\Manufacturing\BuildFiles;

use Rialto\Stock\VersionedItem;

/**
 * An event requesting the upload of build files.
 */
interface BuildFilesRequest
{
    /** @return VersionedItem[] */
    public function getItemsNeedingBuildFiles();
}
