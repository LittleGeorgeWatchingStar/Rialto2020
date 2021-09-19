<?php

namespace Rialto\Port\FormatConversion;


/**
 * A service capable of converting PostScript data to PDF data.
 */
interface PostScriptToPdfConverter
{
    public function toPdf(string $postscript): string;
}
