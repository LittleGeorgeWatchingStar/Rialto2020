<?php

namespace Rialto\Purchasing\Order\Attachment;

use Rialto\Email\Attachment\Attachment;
use Rialto\Email\Attachment\AttachmentSelector;
use Rialto\Manufacturing\BuildFiles\BuildFiles;

/**
 * Extends AttachmentSelector to allow easy attachment of engineering build files.
 *
 * @see BuildFiles
 */
class BuildFileSelector extends AttachmentSelector
{
    /**
     * Attaches all build files except those in $exclude.
     */
    public function attachBuildFiles(BuildFiles $buildFiles, array $exclude = [])
    {
        foreach ($buildFiles->getSupportedFilenames() as $filename) {
            if (isset($exclude[$filename])) {
                continue;
            }
            $this->attachBuildFile($buildFiles, $filename);
        }
    }

    /**
     * Attaches the named build file.
     */
    public function attachBuildFile(
        BuildFiles $buildFiles,
        $filename,
        $selectByDefault = true)
    {
        $file = $buildFiles->getFile($filename);
        $sku = $buildFiles->getFullSku();
        $basename = $file->getBasename();
        $attachmentName = "$sku.$basename";
        $attachment = Attachment::fromFile($attachmentName, $file);
        $this->add($attachment, $selectByDefault);
    }
}
