<?php

namespace Rialto\Filetype\Pdf;

use Rialto\Filesystem\TempFilesystem;
use Rialto\Filetype\ConverterException;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

/**
 * Parses a PDF file into plain text.
 *
 * Uses the "pdftotext" command-line tool which is part of the "poppler-utils"
 * package.
 */
class PdfConverter
{
    /** @var TempFilesystem */
    private $fs;

    public function __construct(TempFilesystem $fs)
    {
        $this->fs = $fs;
    }

    /**
     * @param bool $preserveLayout (default = true)
     *  If true, the layout of the PDF will be approximately preserved
     *  in the output.
     * @return string[]
     *  The lines extracted from the text file.
     */
    public function toLines(SplFileInfo $file, $preserveLayout = true)
    {
        $string = $this->toString($file, $preserveLayout);
        return explode(PHP_EOL, $string);
    }

    private function toString(SplFileInfo $file, $preserveLayout = true)
    {
        $inpath = escapeshellarg($file->getRealPath());
        $outpath = $this->fs->getTempfile('Rialto_PdfConverter_');

        $args = '';
        if ( $preserveLayout ) {
            $args .= '-layout';
        }
        $command = "/usr/bin/pdftotext $args $inpath $outpath";
        $p = new Process($command);
        $p->run();
        if ( $p->isSuccessful() ) {
            $string = $this->fs->getContents(new File($outpath));
            $this->fs->remove($outpath);
            return $string;
        }
        throw new ConverterException($p->getErrorOutput());
    }
}
