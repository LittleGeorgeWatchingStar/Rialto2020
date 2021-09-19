<?php

namespace Rialto\Stock\Publication;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A publication that is an uploaded file.
 */
class UploadPublication extends Publication
{
    /**
     * @var UploadedFile
     * @Assert\File(
     *   mimeTypes={"application/pdf", "image/jpeg", "image/png"})
     */
    private $file = null;

    public function getFilename()
    {
        return $this->content;
    }

    public function setFilename($filename)
    {
        $this->content = trim($filename);
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file)
    {
        $this->file = $file;
    }

    public function setPurpose($purpose)
    {
        $this->purpose = trim($purpose);
    }

    public static function getPurposeOptions()
    {
        return [
            'Public documentation' => self::PURPOSE_PUBLIC,
            'Internal documentation' => self::PURPOSE_INTERNAL,
            'Additional build instructions' => self::PURPOSE_BUILD,
            'To ship with sales orders' => self::PURPOSE_SHIP,
        ];
    }
}
