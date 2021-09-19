<?php

namespace Rialto\StockBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Connection;

/**
 *
 */
class RepairMissingVersionsCommand
extends ContainerAwareCommand
{
    /** @var Connection */
    private $conn;

    /** @var OutputInterface */
    private $output;

    protected function configure()
    {
        $this->setName('rialto:repair-missing-versions')
            ->setDescription('Finds missing version codes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $this->conn = $container->get('database_connection');
        $this->output = $output;

        $records = $this->findFixableRecords();

        $this->conn->beginTransaction();
        try {
            foreach ( $records as $record ) {
                try {
                    $this->fixRecord($record);
                }
                catch ( MatchException $ex ) {
                    $this->logException($ex);
                }
            }
            $this->conn->commit();
        }
        catch ( \Exception $ex ) {
            $this->conn->rollBack();
            $this->logException($ex);
        }

        $this->conn->beginTransaction();
        try {
            $this->fixWorkOrderVersions();
            $this->conn->commit();
        }
        catch ( \Exception $ex ) {
            $this->conn->rollBack();
            $this->logException($ex);
        }
    }

    private function logException(\Exception $ex)
    {
        $this->output->writeln('<error>' . $ex->getMessage() . '</error>');
    }

    private function findFixableRecords()
    {
        $sql = "select i.StockID, wo.WORef, wo.Version, wo.Instructions
            from StockMaster i
            left join ItemVersion v
                on v.stockCode = i.StockID
                and v.version != ''
            join WorksOrders wo
                on wo.StockID = i.StockID
                and wo.Instructions like concat('%', wo.StockID, '-R%')
            where i.MBflag = 'M'
            group by i.StockID
            having count(v.version) = 0";

        $stmt = $this->conn->executeQuery($sql);
        return $stmt->fetchAll();
    }

    private function fixRecord(array $record)
    {
        if ( $record['Version'] != '' ) {
            $this->repairItem($record['StockID'], $record['Version']);
        }
        elseif ( $record['Instructions'] != '' ) {
            $version = $this->extractVersion($record);
            $this->repairItem($record['StockID'], $version);
        }
    }

    private function repairItem($stockCode, $version)
    {
        $this->output->writeln("Version for $stockCode is $version");
        assert( trim($version) != '' );
        $sql = "update ItemVersion
            set version = :version
            where stockCode = :stockCode
            and version = ''";

        $params = array(
            'version' => $version,
            'stockCode' => $stockCode,
        );
        $this->doUpdate($sql, $params);

        $sql = "update StockMaster
            set AutoBuildVersion = :version, ShippingVersion = :version
            where StockID = :stockCode";
        $this->doUpdate($sql, $params);
    }

    private function doUpdate($sql, array $params)
    {
        $updated = $this->conn->executeUpdate($sql, $params);
        if ( $updated !== 1 ) {
            $pstring = json_encode($params);
            throw new \Exception("Update \"$sql\" with params $pstring failed: $updated");
        }
    }

    private function extractVersion(array $record)
    {
        $stockCode = $record['StockID'];
        $matches = array();
        if (! preg_match("/{$stockCode}-R(\d+)/", $record['Instructions'], $matches) ) {
            throw new MatchException("No match for $stockCode");
        }
        if ( count($matches) < 2 ) {
            throw new MatchException("Not enough matches for $stockCode");
        }
        return $matches[1];
    }

    private function fixWorkOrderVersions()
    {
        $sql = "select wo.WORef, wo.StockID, wo.Version, wo.Instructions
            from WorksOrders wo
            where wo.Version = ''
            and wo.Instructions like concat('%', wo.StockID, '-R%');";
        $stmt = $this->conn->executeQuery($sql);
        $records = $stmt->fetchAll();

        foreach ( $records as $record ) {
            try {
                $this->fixWorkOrderVersion($record);
            }
            catch ( MatchException $ex ) {
                $this->logException($ex);
            }
        }
    }

    private function fixWorkOrderVersion(array $record)
    {
        $woID = $record['WORef'];
        $version = $this->extractVersion($record);
        $this->confirmVersionExists($record['StockID'], $version);
        $this->repairWorkOrder($woID, $version);
    }

    private function confirmVersionExists($stockCode, $version)
    {
        $sql = "insert ignore into ItemVersion
            (stockCode, version) values
            (:stockCode, :version)";
        $this->conn->executeUpdate($sql, array(
            'stockCode' => $stockCode,
            'version' => $version,
        ));
    }

    private function repairWorkOrder($woID, $version)
    {
        $this->output->writeln("Version for work order $woID is $version");
        $sql = "update WorksOrders
            set Version = :version
            where WORef = :woID";
        $this->doUpdate($sql, array(
            'version' => $version,
            'woID' => $woID,
        ));
    }
}


class MatchException
extends \Exception
{

}