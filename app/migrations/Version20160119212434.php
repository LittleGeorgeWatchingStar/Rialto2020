<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix BankTrans and BankAccounts foreign keys.
 */
class Version20160119212434 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX BankAccountName ON BankAccounts');
        $this->addSql('DROP INDEX BankAccountNumber ON BankAccounts');
        $this->addSql('ALTER TABLE BankAccounts CHANGE AccountCode AccountCode INT UNSIGNED NOT NULL, CHANGE BankAccountName BankAccountName VARCHAR(100) NOT NULL, CHANGE NextCheckNumber NextCheckNumber INT UNSIGNED DEFAULT 1500 NOT NULL');

        $this->addSql('ALTER TABLE BankAccounts ADD CONSTRAINT FK_7153AED15C2B50D5 FOREIGN KEY (AccountCode) REFERENCES ChartMaster (AccountCode)');
//        $this->addSql('ALTER TABLE BankTrans ADD CONSTRAINT FK_C454918F2F95B449 FOREIGN KEY (BankAct) REFERENCES BankAccounts (AccountCode)');

    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
