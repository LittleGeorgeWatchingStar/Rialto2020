<?php

namespace Rialto\Filetype\Postscript;

use Rialto\Filesystem\FilesystemException;


/**
 * Converts from various formats into Postscript, which is useful for
 * printers.
 */
class PostscriptConverter
{
    const HTML_CONVERTER = 'html2ps';
    const PDF_CONVERTER = 'pdftops'; // PDF to PS

    private $process = null;
    private $pipes = [];

    /**
     * @param string $html
     *  A string of HTML.
     * @param array $options
     *  (optional) An array of long-form options (without the double-dash)
     *  for the program specified by self::HTML_CONVERTER.
     * @return string
     *  A string of Postscript.
     */
    public function convertHtml($html, array $options = [])
    {
        $command = $this->getExecutablePath(self::HTML_CONVERTER);
        $command .= $this->generateOptions($options);
        $this->openProcess($command);
        $this->write($html);
        $ps = $this->read();
        $this->closeProcess();
        return $ps;
    }

    private function getExecutablePath($name)
    {
        $path = exec("which $name");
        if (! $path ) throw new \UnexpectedValueException(
            "Unable to locate $name executable"
        );
        return $path;
    }

    private function generateOptions(array $options)
    {
        $output = '';
        foreach ( $options as $opt => $value ) {
            if ( $value === true ) {
                $output .= " --$opt";
            }
            else {
                $output .= " --$opt $value";
            }
        }
        return $output;
    }

    private function openProcess($childProcess)
    {
        $descriptorspec = [
            0 => ["pipe", "r"], /* child process will read from pipe 0 */
            1 => ["pipe", "w"]  /* child process will write to pipe 1 */
        ];

        $this->process = proc_open($childProcess, $descriptorspec, $this->pipes);
        if (! is_resource($this->process)) {
            throw new FilesystemException("Unable to open $childProcess process.");
        }
    }

    private function write($data)
    {
        $result = fwrite($this->pipes[0], $data);
        if ( false === $result ) {
            throw new FilesystemException(
                "Unable to write to ". get_resource_type($this->process)
            );
        }
        $this->closePipe($this->pipes[0]);
    }

    private function read()
    {
        $result = stream_get_contents($this->pipes[1]);
        if ( false === $result ) {
            throw new FilesystemException(
                "Unable to read from ". get_resource_type($this->process)
            );
        }
        $this->closePipe($this->pipes[1]);
        return $result;
    }

    private function closePipe($pipe)
    {
        if (! fclose($pipe) ) {
            throw new FilesystemException(
                "Unable to close ". get_resource_type($pipe)
            );
        }
    }

    private function closeProcess()
    {
        return proc_close($this->process);
    }

    /**
     * Converts the PDF data string into Postscript.
     *
     * @param string $pdf Data in PDF format
     * @return string Data in Postscript format
     *
     * @throws FilesystemException
     * @throws \UnexpectedValueException
     */
    public function convertPdf($pdf)
    {
        $pdfFile = $this->getTempFile("PostscriptConverter_pdf");
        if ( false === file_put_contents($pdfFile, $pdf) ) {
            throw new FilesystemException($pdfFile);
        }
        $psFile = $this->getTempFile("PostscriptConverter_ps");
        $command = $this->getExecutablePath(self::PDF_CONVERTER);
        $command .= " -paper letter $pdfFile $psFile" ;
        $output = null;
        $result = null;
        exec($command, $output, $result);
        if ( $result != 0 ) {
            throw new \UnexpectedValueException("$command returned error code $result");
        }
        $ps = file_get_contents($psFile);
        if ( false === $ps ) {
            throw new FilesystemException($psFile, 'unable to read');
        }
        unlink($pdfFile);
        unlink($psFile);
        return $ps;
    }

    private function getTempFile($prefix)
    {
        return tempnam(sys_get_temp_dir(), $prefix);
    }
}
