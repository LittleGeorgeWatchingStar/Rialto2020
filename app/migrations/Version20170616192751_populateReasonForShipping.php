<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Populate the SalesOrders.reasonForShipping column if it isn't already.
 */
class Version20170616192751_populateReasonForShipping extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("update SalesOrders set reasonForShipping = 'sale' where reasonForShipping = ''");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // no-op
    }
}
