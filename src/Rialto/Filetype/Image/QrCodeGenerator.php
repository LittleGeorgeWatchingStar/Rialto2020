<?php

namespace Rialto\Filetype\Image;

use Rialto\Filesystem\FilesystemException;


/**
 * Generates QR codes (as image files) from strings.
 */
class QrCodeGenerator
{
    const IMAGE_FORMAT = 'png';
    const RETURN_SUCCESS = 0;

    private $margin;

    public function __construct($margin = 0)
    {
        $this->margin = $margin;
    }

    /**
     * Writes the given string as a QR code image to the named file.
     * It is the responsibility of the caller to provide the output filepath
     * and to delete that file after using it.
     *
     * @param string $filepath
     * @param string $string
     */
    public function writeToFile($filepath, $string)
    {
        $filepath = escapeshellarg($filepath);
        $string = escapeshellarg($string);
        $output = [];
        $returnVal = null;
        $qrencode = $this->getExecutablePath();
        $command = "$qrencode --margin={$this->margin} -o $filepath $string 2>&1";
        exec($command, $output, $returnVal);
        if (self::RETURN_SUCCESS != $returnVal) {
            throw new FilesystemException($filepath, sprintf(
                'unable to write QR code (status: %s; output: %s)',
                $returnVal, join(PHP_EOL, $output)
            ));
        }
    }

    private function getExecutablePath()
    {
        $path = exec('which qrencode');
        if (!$path) throw new \UnexpectedValueException(
            'Unable to locate qrencode executable'
        );
        return $path;
    }
}
