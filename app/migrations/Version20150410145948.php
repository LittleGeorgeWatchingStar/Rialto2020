<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150410145948 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Magento_Storefront ADD quoteTypeID VARCHAR(2) NOT NULL AFTER salesTypeID, DROP consumerKey, DROP consumerSecret, DROP accessToken, DROP tokenSecret');
        $this->addSql('ALTER TABLE Magento_Storefront ADD CONSTRAINT FK_E44A3B420F1D093 FOREIGN KEY (quoteTypeID) REFERENCES SalesTypes (TypeAbbrev)');
        $this->addSql('CREATE INDEX IDX_E44A3B420F1D093 ON Magento_Storefront (quoteTypeID)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Magento_Storefront DROP FOREIGN KEY FK_E44A3B420F1D093');
        $this->addSql('DROP INDEX IDX_E44A3B420F1D093 ON Magento_Storefront');
        $this->addSql('ALTER TABLE Magento_Storefront ADD consumerKey VARCHAR(255) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD consumerSecret VARCHAR(255) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD accessToken VARCHAR(255) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD tokenSecret VARCHAR(255) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, DROP quoteTypeID');
    }
}
