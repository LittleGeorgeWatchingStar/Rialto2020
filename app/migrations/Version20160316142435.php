<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160316142435 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE OrderSent (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, dateSent DATETIME NOT NULL, sender VARCHAR(255) NOT NULL, note VARCHAR(255) NOT NULL, purchaseOrderId BIGINT UNSIGNED NOT NULL, INDEX IDX_963751AE17C2A87D (purchaseOrderId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE OrderSent ADD CONSTRAINT FK_963751AE17C2A87D FOREIGN KEY (purchaseOrderId) REFERENCES PurchOrders (OrderNo)');

        $this->addSql("
            INSERT INTO OrderSent
            (purchaseOrderId, dateSent, sender, note)
            SELECT po.OrderNo, po.DatePrinted, 'unknown', 'none'
            FROM PurchOrders po
            WHERE po.DatePrinted IS NOT NULL
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE OrderSent');
    }
}
