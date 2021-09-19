<?php

namespace Rialto\Filetype\Postscript;

use Rialto\Filesystem\Filesystem;
use Rialto\Filesystem\FilesystemException;
use SplFileInfo;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Filesystem for loading font files.
 */
class FontFilesystem extends Filesystem
{
    /** @return SplFileInfo */
    public function getFontFile($fontname, $extension = '.afm')
    {
        $file = $this->join($this->rootDir, $fontname . $extension);
        if ( $file->isFile() ) {
            return $file;
        }
        throw new FilesystemException("Font file $file does not exist");
    }

    public function initPostscriptFonts(GetResponseEvent $event)
    {
        PostscriptFont::setFontFilesystem($this);
    }
}
