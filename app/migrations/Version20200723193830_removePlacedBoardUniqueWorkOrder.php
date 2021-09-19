<?php declare(strict_types=1);

namespace Rialto\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200723193830_removePlacedBoardUniqueWorkOrder extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE Panelization_PlacedBoard DROP FOREIGN KEY FK_8BFFC6AA67546C0A');
        $this->addSql('DROP INDEX UNIQ_8BFFC6AA67546C0A ON Panelization_PlacedBoard');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8BFFC6AA67546C0A ON Panlization_PlacedBoard(workOrderId)');
        $this->addSql('ALTER TABLE Panelization_PlacedBoard ADD CONSTRAINT FK_8BFFC6AA67546C0A FOREIGN KEY (workOrderId) REFERENCES StockProducer (id) ON DELETE CASCADE');
    }
}
