<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add trackingNumber field to SalesReturn.
 */
class Version20160304093410 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("set session sql_mode=''");
        $this->addSql('DROP INDEX id ON SalesReturn');
        $this->addSql('ALTER TABLE SalesReturn ADD trackingNumber VARCHAR(255) DEFAULT \'\' NOT NULL, CHANGE authorizedBy authorizedBy VARCHAR(20) NOT NULL, CHANGE originalInvoice originalInvoice BIGINT UNSIGNED NOT NULL, CHANGE dateAuthorized dateAuthorized DATETIME NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE SalesReturn DROP trackingNumber');
    }
}
