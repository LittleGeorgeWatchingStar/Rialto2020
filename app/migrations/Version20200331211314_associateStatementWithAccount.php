<?php declare(strict_types=1);

namespace Rialto\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200331211314_associateStatementWithAccount extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE BankStatements ADD accountID INT UNSIGNED NOT NULL DEFAULT 10200');
        $this->addSql('ALTER TABLE BankStatements ADD CONSTRAINT FK_1EE04CDF59B09320 FOREIGN KEY (accountID) REFERENCES BankAccounts (AccountCode)');
        $this->addSql('CREATE INDEX IDX_1EE04CDF59B09320 ON BankStatements (accountID)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE BankStatements DROP FOREIGN KEY FK_1EE04CDF59B09320');
        $this->addSql('DROP INDEX IDX_1EE04CDF59B09320 ON BankStatements');
        $this->addSql('ALTER TABLE BankStatements DROP accountID');
    }
}
