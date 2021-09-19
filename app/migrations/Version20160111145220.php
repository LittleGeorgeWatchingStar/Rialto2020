<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Drop unique constraint from PurchasingCost to avoid Doctrine bug.
 */
class Version20160111145220 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE PurchasingCost DROP FOREIGN KEY PurchasingCost_fk_purchasingDataId');
        $this->addSql('DROP INDEX purchasingDataId_minimumOrderQty_leadTime ON PurchasingCost');
        $this->addSql('ALTER TABLE PurchasingCost ADD CONSTRAINT FK_6549FD10914316CD FOREIGN KEY (purchasingDataId) REFERENCES PurchData (ID)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE UNIQUE INDEX purchasingDataId_minimumOrderQty_leadTime ON PurchasingCost (purchasingDataId, minimumOrderQty, manufacturerLeadTime)');
    }
}
