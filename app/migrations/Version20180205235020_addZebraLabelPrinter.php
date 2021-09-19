<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Change the "ups" discriminator value to "zebra" and add the Zebra label
 * printer.
 */
class Version20180205235020_addZebraLabelPrinter extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("UPDATE Printer SET printerType = 'zebra' WHERE printerType = 'ups'");
        $this->addSql("
            INSERT INTO Printer
            (id, description, host, port, printerType, sleepTime) VALUES
            ('zebra_label', 'Zebra label printer', 'redwood.gumstix.net', '9105', 'zebra', 5)
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("DELETE FROM Printer WHERE id = 'zebra_label'");
        $this->addSql("UPDATE Printer SET printerType = 'ups' WHERE printerType = 'zebra'");
    }
}
