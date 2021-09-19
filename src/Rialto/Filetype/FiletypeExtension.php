<?php

namespace Rialto\Filetype;

use Rialto\Filetype\Image\QrCodeGenerator;
use Twig\Extension\AbstractExtension;
use Twig_Filter;

/**
 * Twig extensions for the Filetype subsystem.
 */
class FiletypeExtension extends AbstractExtension
{
    /** @var QrCodeGenerator */
    private $generator;

    public function __construct(QrCodeGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function getFilters()
    {
        return [
            new Twig_Filter('rialto_util_qrencode', [$this, 'qrEncode']),
        ];
    }

    /**
     * QR-encodes the given string and returns the path of the temporary
     * image file.
     *
     * @param string $string
     * @return string
     */
    public function qrEncode($string)
    {
        $filename = tempnam('/tmp', 'rialto_twig_qr') . '.png';
        $this->generator->writeToFile($filename, $string);
        return $filename;
    }

}
