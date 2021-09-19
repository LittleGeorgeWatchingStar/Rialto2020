<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add dateCreated field to StockMaster and populate it.
 */
class Version20180111191202_addDateCreated extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE StockMaster ADD dateCreated DATETIME DEFAULT NULL');
        $this->addSql("
            UPDATE StockMaster i
            JOIN (
                SELECT pd.StockID, min(sp.dateCreated) AS firstDate
                FROM PurchData pd
                JOIN StockProducer sp ON sp.purchasingDataID = pd.ID
                GROUP BY pd.StockID
            ) pinfo ON pinfo.StockID = i.StockID
            SET i.dateCreated = pinfo.firstDate
        ");
        $this->addSql("
            UPDATE StockMaster i
            JOIN (
                SELECT sod.StkCode AS StockID, min(so.OrdDate) AS firstDate
                FROM SalesOrderDetails sod
                JOIN SalesOrders so ON so.OrderNo = sod.OrderNo
                GROUP BY sod.StkCode
            ) pinfo ON pinfo.StockID = i.StockID
            SET i.dateCreated = least(ifnull(i.dateCreated, pinfo.firstDate), pinfo.firstDate)
        ");
        $this->addSql("
            UPDATE StockMaster i
            JOIN (
                SELECT bom.Parent AS StockID, min(child.dateCreated) AS firstDate
                FROM BOM bom
                JOIN StockMaster child ON child.StockID = bom.Component
                WHERE child.dateCreated IS NOT NULL
                GROUP BY bom.Parent
            ) pinfo ON pinfo.StockID = i.StockID
            SET i.dateCreated = least(ifnull(i.dateCreated, pinfo.firstDate), pinfo.firstDate)
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE StockMaster DROP dateCreated');
    }
}
