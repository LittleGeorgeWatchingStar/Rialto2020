<?php declare(strict_types=1);

namespace Rialto\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201120002917_changeTrackingNumberStringToArray extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE LocTransferHeader ADD trackingNumbers LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function postUp(Schema $schema)
    {
        parent::postUp($schema);

        $data = $this->connection->prepare('SELECT ID, trackingNumber FROM LocTransferHeader');
        $data->execute();
        foreach ($data as $row) {
            $id = $row['ID'];
            $trackingNumber = $row['trackingNumber'];

            if ($trackingNumber === null) {
                $this->connection->update(
                    'LocTransferHeader',
                    [
                        'trackingNumbers' => json_encode([])
                    ],
                    ['id' => $id]);
            } else {
                $this->connection->update(
                    'LocTransferHeader',
                    [
                        'trackingNumbers' => json_encode([
                            $trackingNumber
                        ])
                    ],
                    ['id' => $id]);
            }


        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE LocTransferHeader DROP trackingNumbers');
    }
}
