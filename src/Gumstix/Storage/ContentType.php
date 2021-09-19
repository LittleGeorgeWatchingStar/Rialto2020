<?php

namespace Gumstix\Storage;


class ContentType
{
    /**
     * Determines the content type of a file from the file's contents.
     *
     * @param string $data the file data
     * @return string the mime type
     */
    public static function fromData($data)
    {
        $finfo = finfo_open(FILEINFO_MIME);
        $mime = finfo_buffer($finfo, $data);
        $parts = preg_split('/\;\s/', $mime);
        return $parts[0];
    }
}
