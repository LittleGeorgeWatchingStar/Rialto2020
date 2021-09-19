<?php

namespace Rialto\Email\Attachment;

use Gumstix\Storage\ContentType;
use Gumstix\Storage\File;
use Swift_Attachment;

class StorageAttachment extends Attachment
{
    /** @var File */
    private $file;

    protected function __construct($filename, File $file)
    {
        parent::__construct($filename);
        $this->file = $file;
    }

    public function exists()
    {
        return $this->file->exists();
    }

    public function getContent()
    {
        return $this->file->getContent();
    }

    public function createSwiftAttachment()
    {
        $data = $this->getContent();
        return new Swift_Attachment(
            $data, $this->getFilename(), ContentType::fromData($data));
    }
}
