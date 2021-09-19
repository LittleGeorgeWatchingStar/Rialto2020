<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * mantis4481: link financial tables to Accounting_Transaction.
 */
class Version20160118153823 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE GLTrans ADD transactionId BIGINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE StockMove ADD transactionId BIGINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE SuppTrans ADD transactionId BIGINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE DebtorTrans ADD transactionId BIGINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE BankTrans ADD transactionId BIGINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE CardTrans ADD accountingTransactionId BIGINT UNSIGNED DEFAULT NULL');

        $this->addSql("
UPDATE GLTrans e
  JOIN Accounting_Transaction t
    ON e.Type = t.sysType
       AND e.TypeNo = t.groupNo
       AND e.TranDate = t.transactionDate
SET e.transactionId = t.id
        ");

        $this->addSql("
UPDATE StockMove m
  JOIN Accounting_Transaction t
    ON m.systemTypeID = t.sysType
       AND m.systemTypeNumber = t.groupNo
       AND m.dateMoved = t.transactionDate
SET m.transactionId = t.id;
        ");

        $this->addSql("
UPDATE Accounting_Transaction t
  JOIN GLTrans e ON t.id = e.transactionId
SET t.period = e.PeriodNo
WHERE t.period IS NULL
      AND e.PeriodNo IS NOT NULL;
        ");

        $this->addSql("
UPDATE Accounting_Transaction t
  JOIN StockMove m ON t.id = m.transactionId
SET t.period = m.periodID
WHERE t.period IS NULL
      AND m.periodID IS NOT NULL;
        ");

        $this->addSql("
UPDATE SuppTrans e
  JOIN Accounting_Transaction t
    ON e.Type = t.sysType
       AND e.TransNo = t.groupNo
       AND e.TranDate = t.transactionDate
SET e.transactionId = t.id
        ");

        $this->addSql("
UPDATE DebtorTrans m
  JOIN Accounting_Transaction t
    ON m.Type = t.sysType
       AND m.TransNo = t.groupNo
       AND m.TranDate = t.transactionDate
SET m.transactionId = t.id;
        ");

        $this->addSql("
UPDATE Accounting_Transaction t
  JOIN DebtorTrans m ON t.id = m.transactionId
SET t.period = m.Prd
WHERE t.period IS NULL
      AND m.Prd IS NOT NULL;
        ");



        $this->addSql("
INSERT INTO Accounting_Transaction
(sysType, groupNo, transactionDate)
SELECT ct.Type, ct.TransNo, ct.TransDate FROM BankTrans ct
  LEFT JOIN Accounting_Transaction t
    ON t.sysType = ct.Type
    AND t.groupNo = ct.TransNo
    AND date(t.transactionDate) = ct.TransDate
WHERE t.id IS NULL
        ");

        $this->addSql("
UPDATE BankTrans ct
JOIN Accounting_Transaction t
    ON t.sysType = ct.Type
    AND t.groupNo = ct.TransNo
    AND date(t.transactionDate) = ct.TransDate
SET ct.transactionId = t.id
        ");

        $this->addSql("
INSERT INTO Accounting_Transaction
(sysType, groupNo, transactionDate)
SELECT ct.Type, ct.TransNo, ifnull(ct.dateCaptured, ct.dateCreated) FROM CardTrans ct
LEFT JOIN Accounting_Transaction t
  ON t.sysType = ct.Type
    AND t.groupNo = ct.TransNo
    AND t.transactionDate IN (ct.dateCaptured, ct.dateCreated)
WHERE t.id IS NULL
    AND ct.Type IS NOT NULL
    AND ct.TransNo IS NOT NULL
        ");

        $this->addSql("
update CardTrans ct
JOIN Accounting_Transaction t
  ON t.sysType = ct.Type
    AND t.groupNo = ct.TransNo
    AND t.transactionDate IN (ct.dateCaptured, ct.dateCreated)
set ct.accountingTransactionId = t.id
        ");


        $this->addSql("
update Accounting_Transaction t
join DebtorTrans dt on dt.transactionId = t.id
set t.memo = substring(dt.InvText, 1, 255)
where t.memo = ''
and dt.InvText is not null
and dt.InvText != ''
        ");

        // populate Accounting_Transaction.memo
        $this->addSql("
update Accounting_Transaction t
join SuppTrans dt on dt.transactionId = t.id
set t.memo = substring(dt.SuppReference, 1, 255)
where t.memo = ''
and dt.SuppReference is not null
and dt.SuppReference != ''
        ");

        $this->addSql("
update Accounting_Transaction t
join BankTrans dt on dt.transactionId = t.id
set t.memo = substring(dt.Ref, 1, 255)
where t.memo = ''
and dt.Ref is not null
and dt.Ref != ''
        ");

        $this->addSql("
update Accounting_Transaction t
join GLTrans dt on dt.transactionId = t.id
set t.memo = substring(dt.Narrative, 1, 255)
where t.memo = ''
and dt.Narrative is not null
and dt.Narrative != ''
        ");

        $this->addSql("
update Accounting_Transaction t
join StockMove dt on dt.transactionId = t.id
set t.memo = substring(dt.reference, 1, 255)
where t.memo = ''
and dt.reference is not null
and dt.reference != ''
        ");


        // populate missing Periods in Accounting_Transaction
        $this->addSql("
update Accounting_Transaction t
join Periods p on last_day(t.transactionDate) = p.LastDate_in_Period
set t.period = p.PeriodNo
where t.period is null;
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE GLTrans DROP FOREIGN KEY FK_1398983AC2F43114');
        $this->addSql('ALTER TABLE GLTrans DROP transactionId');

        $this->addSql('ALTER TABLE StockMove DROP FOREIGN KEY FK_F6F8B689C2F43114');
        $this->addSql('ALTER TABLE StockMove drop transactionId');

        $this->addSql('ALTER TABLE SuppTrans DROP FOREIGN KEY FK_D9EEEDA7C2F43114');
        $this->addSql('ALTER TABLE SuppTrans DROP transactionId');

        $this->addSql('ALTER TABLE DebtorTrans DROP FOREIGN KEY FK_2C81749FC2F43114');
        $this->addSql('ALTER TABLE DebtorTrans DROP transactionId');

        $this->addSql('ALTER TABLE BankTrans DROP FOREIGN KEY FK_C454918FC2F43114');
        $this->addSql('ALTER TABLE BankTrans DROP transactionId');

        $this->addSql('ALTER TABLE CardTrans DROP FOREIGN KEY FK_8207C1A3AD7E7526');
        $this->addSql('ALTER TABLE CardTrans DROP accountingTransactionId');
    }
}
