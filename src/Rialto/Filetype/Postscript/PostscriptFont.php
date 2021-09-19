<?php

namespace Rialto\Filetype\Postscript;

/**
 * For loading fonts into Postscript documents.
 */
class PostscriptFont
{
    const DEFAULT_EXTENSION = '.afm';

    /** @var FontFilesystem */
    private static $fontFS;

    /** @var \SplFileInfo */
    private $file;

    /** @var int The font size */
    private $size;

    private $name = null;

    public static function setFontFilesystem(FontFilesystem $fs)
    {
        self::$fontFS = $fs;
    }

    /** @return PostscriptFont */
    public static function getHelvetica($size)
    {
        return self::findFont('Helvetica', $size);
    }

    /** @return PostscriptFont */
    public static function getArial($size)
    {
        return self::findFont('arial', $size);
    }

    /** @return PostscriptFont */
    public static function findFont($fontName, $size)
    {
        $file = self::$fontFS->getFontFile($fontName);
        return new self($file, $fontName, $size);
    }

    public function __construct(\SplFileInfo $file, $name, $size)
    {
        $this->file = $file;
        $this->name = $name;
        $this->size = $size;
    }

    /** @return string */
    public function getName()
    {
        return $this->name;
    }

    /** @return string */
    public function getPathWithoutExtension()
    {
        $ext = $this->file->getExtension();
        $pattern = '/\\.' . $ext . '$/';
        return preg_replace($pattern, '', $this->file->getRealPath());
    }

    /** @return int */
    public function getSize()
    {
        return $this->size;
    }
}
