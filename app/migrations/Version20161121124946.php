<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add SalesOrderDetails.finalUnitPrice
 */
class Version20161121124946 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE SalesOrderDetails DROP FOREIGN KEY FK_AB038F7830D85F93');
        $this->addSql('ALTER TABLE SalesOrderDetails DROP FOREIGN KEY FK_AB038F787636565');
        $this->addSql('ALTER TABLE SalesOrderDetails DROP FOREIGN KEY FK_AB038F78AD3D9A1A');
        $this->addSql('DROP INDEX FK_AB038F787636565 ON SalesOrderDetails');
        $this->addSql('DROP INDEX SalesOrderDetails_fk_DiscountAccount ON SalesOrderDetails');
        $this->addSql('DROP INDEX SalesOrderDetails_fk_CustomizationID ON SalesOrderDetails');

        $this->addSql('ALTER TABLE SalesOrderDetails ADD finalUnitPrice NUMERIC(16, 4) DEFAULT NULL');

        $this->addSql('ALTER TABLE SalesOrderDetails ADD CONSTRAINT FK_AB038F7830D85F93 FOREIGN KEY (CustomizationID) REFERENCES Customization (id)');
        $this->addSql('ALTER TABLE SalesOrderDetails ADD CONSTRAINT FK_AB038F787636565 FOREIGN KEY (StkCode) REFERENCES StockMaster (StockID)');
        $this->addSql('ALTER TABLE SalesOrderDetails ADD CONSTRAINT FK_AB038F78AD3D9A1A FOREIGN KEY (DiscountAccount) REFERENCES ChartMaster (AccountCode)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE SalesOrderDetails DROP finalUnitPrice');
    }
}
