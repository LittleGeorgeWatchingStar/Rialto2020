<?php


namespace Gumstix\Storage;


interface File
{
    /**
     * @return string eg "/some/path/to/file.png"
     */
    public function getKey();

    /**
     * @return string eg "file.png"
     */
    public function getBasename();

    /** @return bool */
    public function exists();

    /** @return string */
    public function getContent();
}
