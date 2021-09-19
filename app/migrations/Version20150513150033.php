<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix a work order issue that was issued from the wrong bin.
 */
class Version20150513150033 extends AbstractMigration
{
    private $wrongBin = 21103;
    private $rightBin = 21069;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->changeBin($this->wrongBin, $this->rightBin);
    }

    private function changeBin($fromBin, $toBin)
    {
        $issueNo = 4680;
        $this->addSql("
            update StockMove
            set binID = $toBin
            where binID = $fromBin
            and id = 387199
            and systemTypeNumber = $issueNo
        ");

        $this->addSql("
            update StockSerialItems
            set Quantity = Quantity + 5
            where SerialNo = $fromBin
        ");

        $this->addSql("
            update StockSerialItems
            set Quantity = Quantity - 5
            where SerialNo = $toBin
        ");

        $this->addSql("
            update StockAllocation
            set SourceNo = $toBin
            where SourceNo = $fromBin
            and AllocationID = 110828
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->changeBin($this->rightBin, $this->wrongBin);
    }
}
