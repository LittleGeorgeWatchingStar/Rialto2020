<?php


namespace Rialto\Security\User\Web;


use Gumstix\Filetype\CsvFileWithHeadings;
use Rialto\Security\User\User;

final class UserCsv
{
    /**
     * @param User[] $entries
     */
    public static function create($entries): CsvFileWithHeadings
    {
        $rows = [];
        foreach ($entries as $entry) {
            $rows[] = [
                'name' => $entry->getName(),
                'email' => $entry->getEmail(),
                'UUIDs' => join("\r", $entry->getUuids()),
                'Roles' => join("\r", $entry->getRoles()),
                'Supplier' => $entry->getSupplier() ?: '',
            ];
        }

        $csv = new CsvFileWithheadings();
        $csv->parseArray($rows);
        return $csv;
    }

}