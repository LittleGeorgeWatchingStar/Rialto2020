<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Save 3 allocation configurations to database
 * printer.
 */
class Version20200817236322_addAllocatorConfigs extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            INSERT INTO AllocationConfiguration
            (id, Type, Priority, Disabled) VALUES
            ('1', 'Warehouse Stock', 1, 0)
        ");
        $this->addSql("
            INSERT INTO AllocationConfiguration
            (id, Type, Priority, Disabled) VALUES
            ('2', 'Purchase Order Items', 2, 0)
        ");
        $this->addSql("
            INSERT INTO AllocationConfiguration
            (id, Type, Priority, Disabled) VALUES
            ('3', 'Contract Manufacturer Stock', 0, 1)
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("DELETE FROM AllocationConfiguration WHERE id = '1'");
        $this->addSql("DELETE FROM AllocationConfiguration WHERE id = '2'");
        $this->addSql("DELETE FROM AllocationConfiguration WHERE id = '3'");
    }
}
