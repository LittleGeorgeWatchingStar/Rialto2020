<?php

namespace Rialto\Email\Attachment;

use Gumstix\Storage\File;

abstract class Attachment
{
    /**
     * @var string
     */
    private $filename;

    /**
     * Factory method that creates an attachment from the raw file data.
     *
     * @param string $filename
     * @param string $content
     * @return Attachment
     */
    public static function fromString($filename, $content)
    {
        return new StringAttachment($filename, $content);
    }

    /**
     * Factory method that creates an attachment from a file.
     *
     * @param string $filename
     * @return Attachment
     */
    public static function fromFile($filename, File $file)
    {
        return new StorageAttachment($filename, $file);
    }

    public static function fromLocalFile($filename, $filepath)
    {
        return new LocalFileAttachment($filename, $filepath);
    }

    protected function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = trim($filename);
    }

    /**
     * @return bool
     */
    public abstract function exists();

    /**
     * @return string
     */
    public abstract function getContent();

    /**
     * @return \Swift_Attachment
     */
    public abstract function createSwiftAttachment();
}
