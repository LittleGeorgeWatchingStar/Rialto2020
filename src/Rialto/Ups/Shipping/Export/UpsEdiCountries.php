<?php

namespace Rialto\Ups\Shipping\Export;

use Rialto\Filesystem\FilesystemException;


/**
 * Determines whether a given country accepts paperless (EDI) invoices.
 */
class UpsEdiCountries
{
    const DATA_FILE = 'edi_countries.txt';

    /**
     * The actual list of countries that accept paperless invoices.
     * @var array
     *  A list of two-letter country codes
     */
    private static $whitelist = null;

    /**
     * Returns true if the given country code is in the list.
     *
     * @param string $countryCode
     * @return bool
     */
    public static function isInList($countryCode)
    {
        if ( null === self::$whitelist ) {
            self::loadListFromFile();
        }
        $countryCode = strtoupper($countryCode);
        return in_array($countryCode, self::$whitelist);
    }

    private static function loadListFromFile()
    {
        $filepath = __DIR__ . DIRECTORY_SEPARATOR . self::DATA_FILE;
        if (! is_file($filepath) ) {
            throw new FilesystemException($filepath);
        }
        $options = FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES;
        self::$whitelist = file($filepath, $options);
    }
}
