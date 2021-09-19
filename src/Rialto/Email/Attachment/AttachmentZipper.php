<?php

namespace Rialto\Email\Attachment;

use Gumstix\Storage\GaufretteStorage;
use Rialto\Filesystem\TempFilesystem;

/**
 * Takes a list of AttachmentSelectors and zips the files of each into
 * a zipfile.
 */
class AttachmentZipper
{
    /** @var TempFilesystem */
    private $tempFS;

    public function __construct(TempFilesystem $tempFS)
    {
        $this->tempFS = $tempFS;
    }

    /**
     * @param $index AttachmentSelector[] Unzipped attachments
     * @return AttachmentSelector Contains the zipped attachments
     */
    public function consolidateAttachments(array $index)
    {
        $attachments = new AttachmentSelector();
        foreach ($index as $key => $subAttachments) {
            $zipFilePath = $this->getZipFilePath($key);
            $this->createZipFile($zipFilePath, $subAttachments);
            $attachmentName = "$key.zip";
            $attachments->add(Attachment::fromLocalFile($attachmentName, $zipFilePath));
        }
        return $attachments;
    }

    private function getZipFilePath($key)
    {
        $filepath = $this->tempFS->getTempfile("attachment-zip/$key", 'zip');
        $this->tempFS->mkdir(dirname($filepath));
        return $filepath;
    }

    private function createZipFile($zipFilePath, AttachmentSelector $attachments)
    {
        $this->tempFS->remove($zipFilePath);
        $zipFile = GaufretteStorage::zipfile($zipFilePath);
        foreach ($attachments->getSelectedAttachments() as $attachment) {
            $zipFile->put($attachment->getFilename(), $attachment->getContent());
        }
    }

}
