<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add support for Magento2-based storefronts.
 */
class Version20170623232455_addMagento2 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Magento2_Storefront (
            id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, 
            storeUrl VARCHAR(255) DEFAULT \'\' NOT NULL, 
            apiKey VARCHAR(255) DEFAULT \'\' NOT NULL, 
            consumerKey VARCHAR(255) DEFAULT \'\' NOT NULL, 
            consumerSecret VARCHAR(255) DEFAULT \'\' NOT NULL, 
            oauthVerifier VARCHAR(255) DEFAULT \'\' NOT NULL, 
            accessToken VARCHAR(255) DEFAULT \'\' NOT NULL, 
            accessTokenSecret VARCHAR(255) DEFAULT \'\' NOT NULL, 
            userID VARCHAR(20) NOT NULL, 
            salesTypeID VARCHAR(2) NOT NULL, 
            quoteTypeID VARCHAR(2) NOT NULL, 
            salesmanID VARCHAR(3) NOT NULL, 
            stockLocationID VARCHAR(5) NOT NULL, 
            UNIQUE INDEX UNIQ_333488421AE86E6F (storeUrl), 
            UNIQUE INDEX UNIQ_333488425FD86D04 (userID), 
            INDEX IDX_33348842DB4AEC44 (salesTypeID), 
            INDEX IDX_3334884220F1D093 (quoteTypeID), 
            INDEX IDX_3334884296EBDEA6 (salesmanID), 
            INDEX IDX_3334884237E54C80 (stockLocationID), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Magento2_Storefront ADD CONSTRAINT FK_333488425FD86D04 FOREIGN KEY (userID) REFERENCES WWW_Users (UserID)');
        $this->addSql('ALTER TABLE Magento2_Storefront ADD CONSTRAINT FK_33348842DB4AEC44 FOREIGN KEY (salesTypeID) REFERENCES SalesTypes (TypeAbbrev)');
        $this->addSql('ALTER TABLE Magento2_Storefront ADD CONSTRAINT FK_3334884220F1D093 FOREIGN KEY (quoteTypeID) REFERENCES SalesTypes (TypeAbbrev)');
        $this->addSql('ALTER TABLE Magento2_Storefront ADD CONSTRAINT FK_3334884296EBDEA6 FOREIGN KEY (salesmanID) REFERENCES Salesman (SalesmanCode)');
        $this->addSql('ALTER TABLE Magento2_Storefront ADD CONSTRAINT FK_3334884237E54C80 FOREIGN KEY (stockLocationID) REFERENCES Locations (LocCode)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE Magento2_Storefront');
    }
}
