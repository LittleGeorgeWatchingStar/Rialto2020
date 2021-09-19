<?php

namespace Rialto\Filetype\Postscript;

/**
 * Class for managing fonts.
 */
class Fonts
{
    /**
     * Relative to %kernel.project_dir%/thirdparty
     */
    const FONTS_DIR = '../fonts';

    /**
     * Returns the path the the font file whose name is given.
     *
     * @param string $fontname
     * @return string
     */
    public static function find($fontname)
    {
        return sprintf('%s/%s.afm', self::FONTS_DIR, $fontname);
    }
}
