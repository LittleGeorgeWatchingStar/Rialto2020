<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151009100113 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE QuotationRequest (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, comments LONGTEXT DEFAULT NULL, dateSent DATETIME DEFAULT NULL, dateReceived DATETIME DEFAULT NULL, supplierId BIGINT UNSIGNED NOT NULL, requestedBy VARCHAR(20) NOT NULL, INDEX IDX_E056E067DF05A1D3 (supplierId), INDEX IDX_E056E0679B50778 (requestedBy), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 101');
        $this->addSql('CREATE TABLE QuotationRequestItem (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, sku VARCHAR(20) NOT NULL, version VARCHAR(31) DEFAULT \'-any-\' NOT NULL, quantities LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', leadTimes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', quotationRequestId BIGINT UNSIGNED NOT NULL, customizationId BIGINT UNSIGNED DEFAULT NULL, purchasingDataId BIGINT UNSIGNED DEFAULT NULL, INDEX IDX_FCD18D90FD4D048C (quotationRequestId), INDEX IDX_FCD18D90F9038C4 (sku), INDEX IDX_FCD18D90837341C5 (customizationId), INDEX IDX_FCD18D90914316CD (purchasingDataId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE QuotationRequest ADD CONSTRAINT FK_E056E067DF05A1D3 FOREIGN KEY (supplierId) REFERENCES Suppliers (SupplierID)');
        $this->addSql('ALTER TABLE QuotationRequest ADD CONSTRAINT FK_E056E0679B50778 FOREIGN KEY (requestedBy) REFERENCES WWW_Users (UserID)');
        $this->addSql('ALTER TABLE QuotationRequestItem ADD CONSTRAINT FK_FCD18D90FD4D048C FOREIGN KEY (quotationRequestId) REFERENCES QuotationRequest (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE QuotationRequestItem ADD CONSTRAINT FK_FCD18D90F9038C4 FOREIGN KEY (sku) REFERENCES StockMaster (StockID)');
        $this->addSql('ALTER TABLE QuotationRequestItem ADD CONSTRAINT FK_FCD18D90837341C5 FOREIGN KEY (customizationId) REFERENCES Customization (id)');
        $this->addSql('ALTER TABLE QuotationRequestItem ADD CONSTRAINT FK_FCD18D90914316CD FOREIGN KEY (purchasingDataId) REFERENCES PurchData (ID)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE QuotationRequestItem DROP FOREIGN KEY FK_FCD18D90FD4D048C');
        $this->addSql('DROP TABLE QuotationRequest');
        $this->addSql('DROP TABLE QuotationRequestItem');
    }
}
