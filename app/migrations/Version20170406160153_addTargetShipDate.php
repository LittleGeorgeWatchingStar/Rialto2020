<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add SalesOrders.targetShipDate
 */
class Version20170406160153_addTargetShipDate extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE SalesOrders DROP FOREIGN KEY FK_18632A2556EC1FCE');
        $this->addSql('DROP INDEX FK_18632A2556EC1FCE ON SalesOrders');
        $this->addSql('ALTER TABLE SalesOrders DROP FOREIGN KEY FK_18632A255AAB1588');
        $this->addSql('DROP INDEX FK_18632A255AAB1588 ON SalesOrders');
        $this->addSql('ALTER TABLE SalesOrders DROP FOREIGN KEY FK_18632A25DE9832DB');
        $this->addSql('DROP INDEX FK_18632A25DE9832DB ON SalesOrders');
        $this->addSql('ALTER TABLE SalesOrders DROP FOREIGN KEY SalesOrders_fk_CreatedBy');
        $this->addSql('DROP INDEX SalesOrders_fk_CreatedBy ON SalesOrders');
        $this->addSql('ALTER TABLE SalesOrders DROP FOREIGN KEY SalesOrders_fk_FromStkLoc');
        $this->addSql('DROP INDEX LocationIndex ON SalesOrders');
        $this->addSql('ALTER TABLE SalesOrders DROP FOREIGN KEY SalesOrders_fk_ShipVia');
        $this->addSql('DROP INDEX ShipVia ON SalesOrders');

        $this->addSql('ALTER TABLE SalesOrders ADD COLUMN targetShipDate DATE DEFAULT NULL AFTER OrdDate');

        $this->addSql('ALTER TABLE SalesOrders ADD CONSTRAINT FK_18632A255AAB1588 FOREIGN KEY (billingAddressID) REFERENCES Geography_Address (id)');
        $this->addSql('ALTER TABLE SalesOrders ADD CONSTRAINT FK_18632A25DE9832DB FOREIGN KEY (shippingAddressID) REFERENCES Geography_Address (id)');
        $this->addSql('ALTER TABLE SalesOrders ADD CONSTRAINT FK_18632A2556EC1FCE FOREIGN KEY (branchID) REFERENCES CustBranch (id)');
        $this->addSql('ALTER TABLE SalesOrders ADD CONSTRAINT FK_18632A2551A7C4E1 FOREIGN KEY (CreatedBy) REFERENCES WWW_Users (UserID)');
        $this->addSql('ALTER TABLE SalesOrders ADD CONSTRAINT FK_18632A257250C7E1 FOREIGN KEY (ShipVia) REFERENCES Shippers (Shipper_ID)');
        $this->addSql('ALTER TABLE SalesOrders ADD CONSTRAINT FK_18632A2568E0148A FOREIGN KEY (FromStkLoc) REFERENCES Locations (LocCode)');
        $this->addSql('CREATE INDEX IDX_18632A255AAB1588 ON SalesOrders (billingAddressID)');
        $this->addSql('CREATE INDEX IDX_18632A25DE9832DB ON SalesOrders (shippingAddressID)');
        $this->addSql('CREATE INDEX IDX_18632A2556EC1FCE ON SalesOrders (branchID)');
        $this->addSql('CREATE INDEX IDX_18632A2551A7C4E1 ON SalesOrders (CreatedBy)');
        $this->addSql('CREATE INDEX IDX_18632A257250C7E1 ON SalesOrders (ShipVia)');
        $this->addSql('CREATE INDEX IDX_18632A2568E0148A ON SalesOrders (FromStkLoc)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE SalesOrders DROP targetShipDate');
    }
}
