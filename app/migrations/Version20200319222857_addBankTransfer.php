<?php declare(strict_types=1);

namespace Rialto\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200319222857_addBankTransfer extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE BankTransfer (ID BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, fromTransactionID BIGINT UNSIGNED DEFAULT NULL, toTransactionID BIGINT UNSIGNED DEFAULT NULL, UNIQUE INDEX UNIQ_FBAF93BD4A0F26E (fromTransactionID), UNIQUE INDEX UNIQ_FBAF93BDC245F8A5 (toTransactionID), PRIMARY KEY(ID)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE BankTransfer');
    }
}
