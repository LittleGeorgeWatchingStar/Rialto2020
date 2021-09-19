<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * mantis4481: clean up indexes on accounting tables
 */
class Version20160118153816 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // GLTrans
        $this->addSql('ALTER TABLE GLTrans DROP FOREIGN KEY GLTrans_fk_Account');
        $this->addSql('DROP INDEX ChequeNo ON GLTrans');
        $this->addSql('DROP INDEX Posted ON GLTrans');
        $this->addSql('DROP INDEX TranDate ON GLTrans');
        $this->addSql('DROP INDEX TypeNo ON GLTrans');
        $this->addSql('DROP INDEX JobRef ON GLTrans');
        $this->addSql('DROP INDEX PeriodNo ON GLTrans');
        $this->addSql('DROP INDEX Account ON GLTrans');
        $this->addSql('DROP INDEX Type_and_Number ON GLTrans');
        $this->addSql('ALTER TABLE GLTrans CHANGE Type Type SMALLINT NOT NULL, CHANGE TypeNo TypeNo BIGINT NOT NULL, CHANGE ChequeNo ChequeNo INT UNSIGNED DEFAULT 0 NOT NULL, CHANGE PeriodNo PeriodNo SMALLINT NOT NULL, CHANGE Account Account INT UNSIGNED NOT NULL, CHANGE Narrative Narrative VARCHAR(200) NOT NULL, CHANGE Amount Amount NUMERIC(16, 2) NOT NULL');
        $this->addSql('CREATE INDEX IDX_1398983A2CECF81744CBDEE9 ON GLTrans (Type, TypeNo)');

        // StockMove
        $this->addSql('ALTER TABLE StockMove DROP FOREIGN KEY FK_F6F8B68956EC1FCE');
        $this->addSql('ALTER TABLE StockMove DROP FOREIGN KEY StockMove_fk_parentID');
        $this->addSql('ALTER TABLE StockMove DROP FOREIGN KEY StockMove_fk_parentItem');
        $this->addSql('ALTER TABLE StockMove DROP FOREIGN KEY StockMove_fk_stockCode');
        $this->addSql('ALTER TABLE StockMove DROP FOREIGN KEY StockMove_fk_binID');
        $this->addSql('ALTER TABLE StockMove DROP FOREIGN KEY StockMove_fk_locationID');
        $this->addSql('ALTER TABLE StockMove DROP FOREIGN KEY StockMove_fk_periodID');
        $this->addSql('ALTER TABLE StockMove DROP FOREIGN KEY StockMove_fk_systemTypeID');

        $this->addSql('DROP INDEX StockMove_fk_parentID ON StockMove');
        $this->addSql('DROP INDEX FK_F6F8B68956EC1FCE ON StockMove');
        $this->addSql('DROP INDEX StockMove_fk_stockCode ON StockMove');
        $this->addSql('DROP INDEX StockMove_fk_locationID ON StockMove');
        $this->addSql('DROP INDEX StockMove_fk_binID ON StockMove');
        $this->addSql('DROP INDEX StockMove_fk_periodID ON StockMove');
        $this->addSql('DROP INDEX StockMove_fk_parentItem ON StockMove');
        $this->addSql('DROP INDEX systemType ON StockMove');

        $this->addSql('
ALTER TABLE StockMove
DROP narrative,
DROP customerID,
DROP branchID,
DROP unitPrice,
DROP discountRate,
DROP discountAccountID,
DROP hidden,
DROP taxRate,
DROP GLTransDR,
DROP GLTransCR,
DROP parentID,
CHANGE systemTypeID systemTypeID SMALLINT NOT NULL,
CHANGE systemTypeNumber systemTypeNumber BIGINT NOT NULL,
CHANGE periodID periodID SMALLINT NOT NULL,
CHANGE stockCode stockCode VARCHAR(20) NOT NULL,
CHANGE locationID locationID VARCHAR(5) NOT NULL,
CHANGE quantity quantity NUMERIC(18, 4) NOT NULL');

        $this->addSql('CREATE INDEX IDX_F6F8B68979C1C6681416DB79 ON StockMove (systemTypeID, systemTypeNumber)');

        // SuppTrans
        $this->addSql('DROP INDEX TypeTransNo ON SuppTrans');
        $this->addSql('DROP INDEX ID ON SuppTrans');
        $this->addSql('DROP INDEX DueDate ON SuppTrans');
        $this->addSql('DROP INDEX Hold ON SuppTrans');
        $this->addSql('DROP INDEX Settled ON SuppTrans');
        $this->addSql('DROP INDEX SupplierNo_2 ON SuppTrans');
        $this->addSql('DROP INDEX SuppReference ON SuppTrans');
        $this->addSql('DROP INDEX TranDate ON SuppTrans');
        $this->addSql('DROP INDEX TransNo ON SuppTrans');
        $this->addSql('DROP INDEX Type ON SuppTrans');
        $this->addSql('DROP INDEX SupplierNo ON SuppTrans');
        $this->addSql('ALTER TABLE SuppTrans CHANGE TransNo TransNo BIGINT UNSIGNED NOT NULL, CHANGE Type Type SMALLINT NOT NULL, CHANGE SupplierNo SupplierNo BIGINT UNSIGNED NOT NULL');
        $this->addSql('CREATE INDEX IDX_D9EEEDA72CECF81796F7A896 ON SuppTrans (Type, TransNo)');

        // DebtorTrans
        $this->addSql('DROP INDEX IDX_2C81749F2CECF81796F7A896 ON DebtorTrans');
        $this->addSql('ALTER TABLE DebtorTrans DROP FOREIGN KEY DebtorTrans_fk_ShipVia');
        $this->addSql('ALTER TABLE DebtorTrans DROP FOREIGN KEY FK_2C81749F2CECF817');
        $this->addSql('ALTER TABLE DebtorTrans DROP FOREIGN KEY FK_2C81749FC42B4997');
        $this->addSql('ALTER TABLE DebtorTrans DROP FOREIGN KEY FK_2C81749FD05B943B');
        $this->addSql('DROP INDEX Prd ON DebtorTrans');
        $this->addSql('DROP INDEX Type ON DebtorTrans');
        $this->addSql('DROP INDEX Order_ ON DebtorTrans');
        $this->addSql('DROP INDEX DebtorTrans_fk_ShipVia ON DebtorTrans');
        $this->addSql('ALTER TABLE DebtorTrans CHANGE TransNo TransNo BIGINT NOT NULL, CHANGE Type Type SMALLINT NOT NULL, CHANGE Prd Prd SMALLINT NOT NULL');
        $this->addSql('CREATE INDEX IDX_2C81749F2CECF81796F7A896 ON DebtorTrans (Type, TransNo)');

        // BankTrans
        $this->addSql("set session sql_mode = ''");
        $this->addSql('DROP INDEX BankTransID ON BankTrans');
        $this->addSql('DROP INDEX BankTransID_2 ON BankTrans');
        $this->addSql('DROP INDEX BankAct ON BankTrans');
        $this->addSql('DROP INDEX TransDate ON BankTrans');
        $this->addSql('DROP INDEX TransType ON BankTrans');
        $this->addSql('DROP INDEX Type ON BankTrans');
        $this->addSql('DROP INDEX CurrCode ON BankTrans');
        $this->addSql('DROP INDEX ChequeNo ON BankTrans');
        $this->addSql('ALTER TABLE BankTrans CHANGE Type Type SMALLINT NOT NULL, CHANGE TransNo TransNo BIGINT NOT NULL, CHANGE BankAct BankAct INT UNSIGNED NOT NULL, CHANGE TransDate TransDate DATE NOT NULL, CHANGE Printed Printed TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('CREATE INDEX IDX_C454918F2CECF81796F7A896 ON BankTrans (Type, TransNo)');
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
