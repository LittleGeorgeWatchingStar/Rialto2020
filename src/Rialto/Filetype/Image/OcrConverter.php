<?php

namespace Rialto\Filetype\Image;

use InvalidArgumentException;
use Rialto\Filesystem\TempFilesystem;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

/**
 * Extracts text from non-text files (such as images) using optical
 * character recognition (OCR).
 *
 * Uses the "tesseract" command-line tool, which is part of the
 * "tesseract-ocr" package.
 */
class OcrConverter
{
    /** @var TempFilesystem */
    private $fs;

    public function __construct(TempFilesystem $fs)
    {
        $this->fs = $fs;
    }

    /**
     * Returns lines of text from the file.
     * @param SplFileInfo $file
     * @return string[]
     * @throws InvalidArgumentException
     */
    public function toLines(SplFileInfo $file)
    {
        $file = new File($file->getPathname());
        $ext = $file->guessExtension();
        switch ($ext) {
            case 'pdf':
                $text = $this->parsePdf($file);
                break;
            case 'tif':
            case 'tiff':
                $text = $this->parseTif($file);
                break;
            default:
                throw new InvalidArgumentException("Cannot handle $ext files");
        }

        return array_filter(explode(PHP_EOL, $text));
    }

    private function parsePdf(SplFileInfo $file)
    {
        $filename = $file->getRealPath();
        assertion(false !== $filename);
        $output = $this->fs->getTempfile('Rialto_OcrParser_', 'tif');
        $p = new Process("convert -monochrome -density 300 $filename $output");
        $p->mustRun();
        $text = $this->parseTif(new File($output));
        $this->fs->remove($output);
        return $text;
    }

    private function parseTif(SplFileInfo $file)
    {
        $filename = $file->getRealPath();
        assertion(false !== $filename);
        $output = $this->fs->getTempfile('Rialto_OcrParser_');
        $p = new Process("tesseract $filename $output");
        $p->mustRun();
        $text = $this->fs->getContents(new File("$output.txt"));
        $this->fs->remove("$output.txt");
        return $text;
    }
}
