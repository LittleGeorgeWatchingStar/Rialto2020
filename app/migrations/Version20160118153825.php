<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * mantis4481: rebuild financial constraints
 */
class Version20160118153825 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE Accounting_Transaction CHANGE period period SMALLINT NOT NULL');

        $this->addSql('ALTER TABLE GLTrans CHANGE transactionId transactionId BIGINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE StockMove CHANGE transactionId transactionId BIGINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE SuppTrans CHANGE transactionId transactionId BIGINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE DebtorTrans CHANGE transactionId transactionId BIGINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE BankTrans CHANGE transactionId transactionId BIGINT UNSIGNED NOT NULL');

        $this->addSql('ALTER TABLE GLTrans ADD CONSTRAINT FK_1398983A2CECF817 FOREIGN KEY (Type) REFERENCES SysTypes (TypeID)');
        $this->addSql('ALTER TABLE GLTrans ADD CONSTRAINT FK_1398983AA42E6CF0 FOREIGN KEY (PeriodNo) REFERENCES Periods (PeriodNo)');
        $this->addSql('ALTER TABLE GLTrans ADD CONSTRAINT FK_1398983AB28B6F38 FOREIGN KEY (Account) REFERENCES ChartMaster (AccountCode)');

        $this->addSql('ALTER TABLE StockMove ADD CONSTRAINT FK_F6F8B68979C1C668 FOREIGN KEY (systemTypeID) REFERENCES SysTypes (TypeID)');
        $this->addSql('ALTER TABLE StockMove ADD CONSTRAINT FK_F6F8B689EC2233CA FOREIGN KEY (stockCode) REFERENCES StockMaster (StockID)');
        $this->addSql('ALTER TABLE StockMove ADD CONSTRAINT FK_F6F8B689ADB908A5 FOREIGN KEY (locationID) REFERENCES Locations (LocCode)');
        $this->addSql('ALTER TABLE StockMove ADD CONSTRAINT FK_F6F8B68971BDFC56 FOREIGN KEY (binID) REFERENCES StockSerialItems (SerialNo)');
        $this->addSql('ALTER TABLE StockMove ADD CONSTRAINT FK_F6F8B689BEA56121 FOREIGN KEY (periodID) REFERENCES Periods (PeriodNo)');
        $this->addSql('ALTER TABLE StockMove ADD CONSTRAINT FK_F6F8B689C9E1F0BF FOREIGN KEY (parentItem) REFERENCES StockMaster (StockID)');

        $this->addSql('ALTER TABLE Accounting_Transaction ADD CONSTRAINT FK_8BA215DF1BF4D5 FOREIGN KEY (sysType) REFERENCES SysTypes (TypeID)');
        $this->addSql('ALTER TABLE Accounting_Transaction ADD CONSTRAINT FK_8BA215C5B81ECE FOREIGN KEY (period) REFERENCES Periods (PeriodNo)');

        $this->addSql('ALTER TABLE GLTrans ADD CONSTRAINT FK_1398983AC2F43114 FOREIGN KEY (transactionId) REFERENCES Accounting_Transaction (id)');
        $this->addSql('ALTER TABLE StockMove ADD CONSTRAINT FK_F6F8B689C2F43114 FOREIGN KEY (transactionId) REFERENCES Accounting_Transaction (id)');

        $this->addSql('ALTER TABLE SuppTrans ADD CONSTRAINT FK_D9EEEDA7C2F43114 FOREIGN KEY (transactionId) REFERENCES Accounting_Transaction (id)');
        $this->addSql('ALTER TABLE SuppTrans ADD CONSTRAINT FK_D9EEEDA72CECF817 FOREIGN KEY (Type) REFERENCES SysTypes (TypeID)');
        $this->addSql('ALTER TABLE SuppTrans ADD CONSTRAINT FK_D9EEEDA748CBED4C FOREIGN KEY (SupplierNo) REFERENCES Suppliers (SupplierID)');

        $this->addSql('ALTER TABLE DebtorTrans ADD CONSTRAINT FK_2C81749FC2F43114 FOREIGN KEY (transactionId) REFERENCES Accounting_Transaction (id)');
        $this->addSql('ALTER TABLE DebtorTrans ADD CONSTRAINT FK_2C81749FC42B4997 FOREIGN KEY (Prd) REFERENCES Periods (PeriodNo)');
        $this->addSql('ALTER TABLE DebtorTrans ADD CONSTRAINT FK_2C81749F2CECF817 FOREIGN KEY (Type) REFERENCES SysTypes (TypeID)');
        $this->addSql('ALTER TABLE DebtorTrans ADD CONSTRAINT FK_2C81749FD05B943B FOREIGN KEY (Order_) REFERENCES SalesOrders (OrderNo)');
        $this->addSql('ALTER TABLE DebtorTrans ADD CONSTRAINT FK_2C81749F7250C7E1 FOREIGN KEY (ShipVia) REFERENCES Shippers (Shipper_ID)');

        $this->addSql('ALTER TABLE BankTrans ADD CONSTRAINT FK_C454918FC2F43114 FOREIGN KEY (transactionId) REFERENCES Accounting_Transaction (id)');
        $this->addSql('ALTER TABLE BankTrans ADD CONSTRAINT FK_C454918F2CECF817 FOREIGN KEY (Type) REFERENCES SysTypes (TypeID)');

        $this->addSql('ALTER TABLE CardTrans ADD CONSTRAINT FK_8207C1A3AD7E7526 FOREIGN KEY (accountingTransactionId) REFERENCES Accounting_Transaction (id)');
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
