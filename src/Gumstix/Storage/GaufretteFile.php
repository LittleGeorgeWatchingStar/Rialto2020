<?php

namespace Gumstix\Storage;

use Gaufrette\File as FileImpl;


class GaufretteFile
implements File
{
    /** @var FileImpl */
    private $file;

    public function __construct(FileImpl $file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->file->getKey();
    }

    /**
     * @return string eg "file.png"
     */
    public function getBasename()
    {
        return basename($this->getKey());
    }

    /** @return bool */
    public function exists()
    {
        return $this->file->exists();
    }

    /** @return string */
    public function getContent()
    {
        return $this->file->getContent();
    }

}
