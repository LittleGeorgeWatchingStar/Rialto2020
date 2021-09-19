<?php

namespace Rialto\Filetype\Pdf;

use Rialto\Filesystem\FilesystemException;
use Symfony\Component\Process\Process;
use Symfony\Component\Templating\EngineInterface;
use UnexpectedValueException;

/**
 * Uses LaTeX templates to generate PDF files.
 *
 * We generate PDFs using a multi-stage pipeline: Twig templates with a
 * .tex.twig extension are fed through Twig to generate LaTeX files. The
 * LaTeX files are fed through the `pdflatex` program to generate PDFs.
 */
class PdfGenerator
{
    /** @var EngineInterface */
    private $templating;

    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param string $template
     * @param mixed[] $params
     * @return string PDF data
     */
    public function render($template, array $params = [])
    {
        $tex = $this->templating->render($template, $params);
        return $this->fromTex($tex);
    }

    public function exists($template)
    {
        return $this->templating->exists($template);
    }

    public function fromTex($tex)
    {
        $dir = "/tmp";
        $tempTexFileName = tempnam($dir, "rialto_tex");
        $tempPdfFileName = "$tempTexFileName.pdf";
        if (! file_put_contents($tempTexFileName, $tex) ) {
            throw new FilesystemException($tempTexFileName, 'unable to write');
        }
        $converter = $this->getConverterPath();
        $cmd = "$converter -output-directory=$dir $tempTexFileName";
        /* Run multiple passes to calculate cross-references */
        for ($i = 0; $i < 2; $i ++ ) {
            $process = new Process($cmd);
            $process->run();
            if (! $process->isSuccessful() ) {
                file_put_contents("$tempTexFileName.err", $process->getErrorOutput());
                $result = $process->getExitCode();
                throw new UnexpectedValueException("Command '$cmd' returned error code $result");
            }
        }
        $output = file_get_contents($tempPdfFileName);
        if (! $output ) {
            throw new FilesystemException($tempPdfFileName, 'unable to read');
        }
        $filesToRemove = [
            $tempTexFileName,
            $tempPdfFileName,
            "$tempTexFileName.aux",
            "$tempTexFileName.log",
        ];
        foreach ( $filesToRemove as $fileToRemove) {
            if ( ! unlink($fileToRemove) ) {
                throw new FilesystemException($fileToRemove, 'unable to delete');
            }
        }
        return $output;
    }

    private function getConverterPath()
    {
        static $converter = "pdflatex";
        $process = new Process("which $converter");
        $process->run();
        if ( $process->isSuccessful() ) {
            return trim($process->getOutput());
        } else {
            throw new UnexpectedValueException("Unable to locate $converter");
        }
    }
}
