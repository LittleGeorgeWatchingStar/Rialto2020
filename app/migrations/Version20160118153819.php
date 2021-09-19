<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * mantis4481: create and populate Accounting_Transaction.
 */
class Version20160118153819 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Accounting_Transaction (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, period SMALLINT DEFAULT NULL, groupNo BIGINT UNSIGNED DEFAULT NULL, transactionDate DATETIME NOT NULL, memo VARCHAR(255) DEFAULT \'\' NOT NULL, sysType SMALLINT NOT NULL, INDEX IDX_8BA215DF1BF4D53512C7FF (sysType, groupNo), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql("

INSERT INTO Accounting_Transaction
(sysType, groupNo, transactionDate)
  (
    SELECT DISTINCT
      Type     AS sysType,
      TypeNo   AS groupNo,
      TranDate AS transactionDate
    FROM GLTrans
  )
  UNION (
    SELECT DISTINCT
      systemTypeID     AS sysType,
      systemTypeNumber AS groupNo,
      dateMoved        AS transactionDate
    FROM StockMove
  )
  UNION (
    SELECT DISTINCT
      Type     AS sysType,
      TransNo  AS groupNo,
      TranDate AS transactionDate
    FROM SuppTrans
  )
  UNION (
    SELECT DISTINCT
      Type     AS sysType,
      TransNo  AS groupNo,
      TranDate AS transactionDate
    FROM DebtorTrans
  )
  ORDER BY transactionDate, sysType, groupNo;

        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE Accounting_Transaction');
    }
}
