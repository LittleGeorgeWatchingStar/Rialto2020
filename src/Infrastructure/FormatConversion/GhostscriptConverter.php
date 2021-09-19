<?php

namespace Infrastructure\FormatConversion;


use Rialto\Port\FormatConversion\PostScriptToPdfConverter;
use Symfony\Component\Process\Process;

/**
 * A wrapper around Ghostscript utilities for PostScript conversions
 */
final class GhostscriptConverter implements PostScriptToPdfConverter
{
    const EXE = '/usr/bin/ps2pdf';

    public function toPdf(string $postscript): string
    {
        /*
         * Use stdin and stdout for the input and output for ps2pdf respectively,
         * this saves us the time and error handling of writing temporary files,
         * reading the output pdf file and cleaning up the temporary files.
         */
        $process = new Process([self::EXE, '-dAutoRotatePages=/None', '-']);

        $process->setInput($postscript);
        $process->mustRun();

        return $process->getOutput();
    }
}
