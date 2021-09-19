<?php

namespace Rialto\Stock\Item\Cli;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixTempRangeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('fix-temp-range');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sql = "
        SELECT StockID AS sku, Temperature AS temp
        FROM StockMaster
        WHERE Temperature != ''
        ";
        $this->updateThese($sql, $output);

        $sql = "
        SELECT StockID AS sku, Temperature AS temp
        FROM PurchData
        WHERE Temperature != ''
        ";
        $this->updateThese($sql, $output);
    }

    private function updateThese($sql, OutputInterface $output)
    {
        /** @var $db Connection */
        $db = $this->getContainer()->get(Connection::class);
        $results = $db->fetchAll($sql);
        foreach ($results as $result) {
            $sku = $result['sku'];
            list($min, $max) = $this->parseTemperature($result['temp']);

            $update = "
                UPDATE StockMaster
                SET minTemperature = :min, maxTemperature = :max
                WHERE StockID = :sku
                AND minTemperature IS NULL
                AND maxTemperature IS NULL
            ";
            $updated = $db->executeUpdate($update, [
                'min' => $min,
                'max' => $max,
                'sku' => $sku,
            ]);
            if ($updated > 0) {
                $output->writeln(sprintf('%20s - min: %4s, max: %4s', $sku, $min, $max));
            }
        }
    }

    private function parseTemperature($tempString)
    {
        $matches = [];
        if (preg_match('/(-?\d+)\D+(\d+)/', $tempString, $matches)) {
            return [$matches[1], $matches[2]];
        }
        return [null, null];
    }
}
