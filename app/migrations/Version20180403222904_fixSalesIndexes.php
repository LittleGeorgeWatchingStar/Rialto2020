<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add foreign key constraint from SalesOrders to SalesTypes.
 */
class Version20180403222904_fixSalesIndexes extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->dropIndexes();
        $this->fixColumnTypes();
        $this->fixData();
        $this->createIndexes();
    }

    private function dropIndexes()
    {
        $this->addSql('ALTER TABLE SalesTypes DROP INDEX Sales_Type');

        $this->addSql('ALTER TABLE SalesGLPostings DROP FOREIGN KEY FK_10B636991662CC54');
        $this->addSql('ALTER TABLE SalesGLPostings DROP FOREIGN KEY FK_10B63699321FBC78');
        $this->addSql('ALTER TABLE SalesGLPostings DROP FOREIGN KEY FK_10B6369977A69256');
        $this->addSql('ALTER TABLE SalesGLPostings DROP FOREIGN KEY FK_10B6369982FF31AD');
        $this->addSql('ALTER TABLE SalesGLPostings DROP FOREIGN KEY FK_10B63699A4892256');

        $this->addSql('DROP INDEX Area ON SalesGLPostings');
        $this->addSql('DROP INDEX SalesType ON SalesGLPostings');
        $this->addSql('DROP INDEX StkCat ON SalesGLPostings');

        $this->addSql('DROP INDEX Area_StkCat ON COGSGLPostings');
        $this->addSql('DROP INDEX Area ON COGSGLPostings');
        $this->addSql('DROP INDEX StkCat ON COGSGLPostings');
        $this->addSql('DROP INDEX SalesType ON COGSGLPostings');
        $this->addSql('DROP INDEX GLCode ON COGSGLPostings');

        $this->addSql('ALTER TABLE CustBranch DROP FOREIGN KEY FK_1B238F8E77A69256');

        $this->addSql('ALTER TABLE DebtorsMaster DROP FOREIGN KEY FK_1EE3B9D11662CC54');

        $this->addSql('ALTER TABLE Prices DROP FOREIGN KEY Prices_fk_CurrAbrev');
        $this->addSql('ALTER TABLE Prices DROP FOREIGN KEY Prices_fk_StockID');
        $this->addSql('ALTER TABLE Prices DROP FOREIGN KEY Prices_fk_TypeAbbrev');
        $this->addSql('DROP INDEX ID ON Prices');
        $this->addSql('DROP INDEX StockID_TypeAbbrev_CurrAbrev ON Prices');

        $this->addSql("DROP TABLE IF EXISTS Magento_Storefront");
        $this->addSql('ALTER TABLE Magento2_Storefront DROP FOREIGN KEY FK_33348842DB4AEC44');
        $this->addSql('ALTER TABLE Magento2_Storefront DROP FOREIGN KEY FK_3334884220F1D093');
        $this->addSql('ALTER TABLE Shopify_Storefront DROP FOREIGN KEY FK_E39263EDB4AEC44');
    }

    private function fixColumnTypes()
    {
        $this->addSql('ALTER TABLE SalesTypes CHANGE TypeAbbrev TypeAbbrev VARCHAR(2) NOT NULL, CHANGE Sales_Type Sales_Type VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE Areas CHANGE AreaCode AreaCode VARCHAR(2) NOT NULL, CHANGE AreaDescription AreaDescription VARCHAR(25) NOT NULL');
        $this->addSql('ALTER TABLE SalesGLPostings CHANGE Area Area VARCHAR(2) DEFAULT NULL, CHANGE SalesType SalesType VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE COGSGLPostings CHANGE Area Area VARCHAR(2) DEFAULT NULL, CHANGE SalesType SalesType VARCHAR(2) DEFAULT NULL, CHANGE GLCode GLCode INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE Prices CHANGE TypeAbbrev TypeAbbrev VARCHAR(2) NOT NULL');
    }

    private function fixData()
    {
        $this->addSql("UPDATE SalesOrders SET OrderType = 'DI' WHERE OrderType = ''");
    }

    private function createIndexes()
    {
        $this->addSql('ALTER TABLE SalesTypes ADD UNIQUE INDEX UNIQ_771E382A9E261612 (Sales_Type)');
        $this->addSql('ALTER TABLE SalesOrders ADD CONSTRAINT FK_18632A25A02D2C2E FOREIGN KEY (OrderType) REFERENCES SalesTypes (TypeAbbrev) ON DELETE RESTRICT');

        $this->addSql('ALTER TABLE SalesGLPostings ADD CONSTRAINT FK_10B636991662CC54 FOREIGN KEY (SalesType) REFERENCES SalesTypes (TypeAbbrev)');
        $this->addSql('ALTER TABLE SalesGLPostings ADD CONSTRAINT FK_10B63699321FBC78 FOREIGN KEY (SalesGLCode) REFERENCES ChartMaster (AccountCode)');
        $this->addSql('ALTER TABLE SalesGLPostings ADD CONSTRAINT FK_10B6369977A69256 FOREIGN KEY (Area) REFERENCES Areas (AreaCode)');
        $this->addSql('ALTER TABLE SalesGLPostings ADD CONSTRAINT FK_10B6369982FF31AD FOREIGN KEY (StkCat) REFERENCES StockCategory (CategoryID)');
        $this->addSql('ALTER TABLE SalesGLPostings ADD CONSTRAINT FK_10B63699A4892256 FOREIGN KEY (DiscountGLCode) REFERENCES ChartMaster (AccountCode)');

        $this->addSql('ALTER TABLE COGSGLPostings ADD CONSTRAINT FK_D912930CC8FF6E85 FOREIGN KEY (GLCode) REFERENCES ChartMaster (AccountCode)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D912930C77A6925682FF31AD1662CC54 ON COGSGLPostings (Area, StkCat, SalesType)');

        $this->addSql('ALTER TABLE CustBranch ADD CONSTRAINT FK_1B238F8E77A69256 FOREIGN KEY (Area) REFERENCES Areas (AreaCode)');

        $this->addSql('ALTER TABLE DebtorsMaster ADD CONSTRAINT FK_1EE3B9D11662CC54 FOREIGN KEY (SalesType) REFERENCES SalesTypes (TypeAbbrev)');

        $this->addSql('ALTER TABLE Prices ADD CONSTRAINT FK_E367686F399B8EF6 FOREIGN KEY (StockID) REFERENCES StockMaster (StockID) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Prices ADD CONSTRAINT FK_E367686F3AEBFCA1 FOREIGN KEY (TypeAbbrev) REFERENCES SalesTypes (TypeAbbrev) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Prices ADD CONSTRAINT FK_E367686FDA9E60F4 FOREIGN KEY (CurrAbrev) REFERENCES Currencies (CurrAbrev) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E367686F399B8EF63AEBFCA1DA9E60F4 ON Prices (StockID, TypeAbbrev, CurrAbrev)');

        $this->addSql('ALTER TABLE Magento2_Storefront ADD CONSTRAINT FK_33348842DB4AEC44 FOREIGN KEY (salesTypeID) REFERENCES SalesTypes (TypeAbbrev)');
        $this->addSql('ALTER TABLE Magento2_Storefront ADD CONSTRAINT FK_3334884220F1D093 FOREIGN KEY (quoteTypeID) REFERENCES SalesTypes (TypeAbbrev)');
        $this->addSql('ALTER TABLE Shopify_Storefront ADD CONSTRAINT FK_E39263EDB4AEC44 FOREIGN KEY (salesTypeID) REFERENCES SalesTypes (TypeAbbrev)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
