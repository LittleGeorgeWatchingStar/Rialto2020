<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Tidy up SalesOrderDetails.
 */
class Version20150928105159 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("update SalesOrderDetails set DiscountAccount = 49000 where DiscountAccount is null");

        $this->addSql('ALTER TABLE SalesOrderDetails DROP FOREIGN KEY SalesOrderDetails_fk_OrderNo');
        $this->addSql('ALTER TABLE SalesOrderDetails DROP FOREIGN KEY SalesOrderDetails_fk_StkCode');
        $this->addSql('ALTER TABLE SalesOrderDetails DROP FOREIGN KEY SalesOrderDetails_fk_CustomizationID');
        $this->addSql('ALTER TABLE SalesOrderDetails DROP FOREIGN KEY SalesOrderDetails_fk_DiscountAccount');
        $this->addSql('DROP INDEX OrderNo_StkCode ON SalesOrderDetails');
        $this->addSql('DROP INDEX ID ON SalesOrderDetails');
        $this->addSql('DROP INDEX Completed ON SalesOrderDetails');
        $this->addSql('DROP INDEX OrderNo ON SalesOrderDetails');
        $this->addSql('DROP INDEX StkCode ON SalesOrderDetails');

        $this->addSql('ALTER TABLE SalesOrderDetails DROP Estimate, DROP Narrative, DROP Custom, CHANGE OrderNo OrderNo BIGINT UNSIGNED NOT NULL, CHANGE StkCode StkCode VARCHAR(20) NOT NULL, CHANGE UnitPrice UnitPrice NUMERIC(16, 4) NOT NULL, CHANGE Quantity Quantity NUMERIC(16, 4) NOT NULL, CHANGE DiscountPercent DiscountPercent NUMERIC(8, 6) DEFAULT \'0\' NOT NULL, CHANGE DiscountAccount DiscountAccount INT UNSIGNED NOT NULL');

        $this->addSql('ALTER TABLE SalesOrderDetails ADD CONSTRAINT FK_AB038F78ED0D02E4 FOREIGN KEY (OrderNo) REFERENCES SalesOrders (OrderNo)');
        $this->addSql('ALTER TABLE SalesOrderDetails ADD CONSTRAINT FK_AB038F787636565 FOREIGN KEY (StkCode) REFERENCES StockMaster (StockID)');
        $this->addSql('ALTER TABLE SalesOrderDetails ADD CONSTRAINT FK_AB038F78AD3D9A1A FOREIGN KEY (DiscountAccount) REFERENCES ChartMaster (AccountCode)');
        $this->addSql('ALTER TABLE SalesOrderDetails ADD CONSTRAINT FK_AB038F7830D85F93 FOREIGN KEY (CustomizationID) REFERENCES Customization (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AB038F78ED0D02E4763656530D85F93 ON SalesOrderDetails (OrderNo, StkCode, CustomizationID)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
