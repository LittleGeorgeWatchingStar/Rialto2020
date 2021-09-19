<?php

namespace Rialto\Filetype\Excel;

use Gumstix\Filetype\CsvFile;
use SplFileInfo;
use Symfony\Component\Process\Process;

/**
 * Converts .xls files (Excel spreadsheets) into .csv format.
 *
 * Uses the "ssconvert" command-line tool, which is part of the "gnumeric" package.
 */
class XlsConverter
{
    const SUPPORTED_MIMETYPES = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public function toCsvFile(SplFileInfo $xlsFile): CsvFile
    {
        $csvString = $this->toString($xlsFile);
        $csvFile = new CsvFile();
        $csvFile->parseString($csvString);
        return $csvFile;
    }

    public function toString(SplFileInfo $xlsFile): string
    {
        $filepath = $xlsFile->getRealPath();
        $stdout = 'fd://1'; // see ssconvert manpage
        $p = new Process("ssconvert --export-type=Gnumeric_stf:stf_csv $filepath $stdout");
        $p->run();
        if ($p->isSuccessful()) {
            return $p->getOutput();
        }
        throw new \RuntimeException($p->getErrorOutput());
    }

    public function isAvailable(): bool
    {
        $p = new Process('which ssconvert');
        $p->run();
        return $p->isSuccessful();
    }
}
