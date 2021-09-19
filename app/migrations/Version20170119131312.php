<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Rialto\Tax\TaxExemption;

/**
 * Eliminate "out of state" tax exemption status.
 */
class Version20170119131312 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("update DebtorsMaster set StateStatus = :t where StateStatus like :oos", [
            't' => TaxExemption::NONE,
            'oos' => 'out of state',
        ]);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
