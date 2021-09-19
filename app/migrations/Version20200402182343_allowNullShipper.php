<?php declare(strict_types=1);

namespace Rialto\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200402182343_allowNullShipper extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE LocTransferHeader DROP FOREIGN KEY FK_B9E48C13F01E5E8A');
        $this->addSql('ALTER TABLE LocTransferHeader CHANGE shipperId shipperId BIGINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE LocTransferHeader ADD CONSTRAINT FK_B9E48C13F01E5E8A FOREIGN KEY (shipperId) REFERENCES Shippers (Shipper_ID)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE LocTransferHeader DROP FOREIGN KEY FK_B9E48C13F01E5E8A');
        $this->addSql('ALTER TABLE LocTransferHeader CHANGE shipperId shipperId BIGINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE LocTransferHeader ADD CONSTRAINT FK_B9E48C13F01E5E8A FOREIGN KEY (shipperId) REFERENCES Shippers (Shipper_ID) ON DELETE RESTRICT');
    }
}
