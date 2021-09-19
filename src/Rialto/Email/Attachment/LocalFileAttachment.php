<?php

namespace Rialto\Email\Attachment;

use Rialto\Filesystem\FilesystemException;

class LocalFileAttachment extends Attachment
{
    /** @var string */
    private $filepath;

    protected function __construct($filename, $filepath)
    {
        parent::__construct($filename);
        $this->filepath = $filepath;
    }

    public function exists()
    {
        return file_exists($this->filepath);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $content = file_get_contents($this->filepath);
        if (false === $content) {
            throw new FilesystemException($this->filepath, 'unable to read');
        }
        return $content;
    }

    /** @return \Swift_Attachment */
    public function createSwiftAttachment()
    {
        $swift = \Swift_Attachment::fromPath($this->filepath);
        $swift->setFilename($this->getFilename());
        return $swift;
    }
}
