<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix users' default locations.
 */
class Version20151112134155 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            update WWW_Users u
            left join Suppliers s on u.SupplierID = s.SupplierID
            left join Locations l on l.SupplierID = s.SupplierID
            set u.DefaultLocation = l.LocCode
        ");

        $this->addSql("
            update WWW_Users u
            set u.DefaultLocation = '7'
            where u.UserID in ('knkt', 'theresalk')
        ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();

    }
}
