<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Tidy up Recurring invoices.
 */
class Version20160120134815 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE RecurringGLInvoices DROP FOREIGN KEY RecurringGLInvoices_fk_RecurringID');
        $this->addSql('ALTER TABLE RecurringGLInvoices DROP FOREIGN KEY RecurringGLInvoices_fk_Account');
        $this->addSql('DROP INDEX ID ON RecurringGLInvoices');
        $this->addSql('DROP INDEX RecurringGLInvoices_fk_RecurringID ON RecurringGLInvoices');
        $this->addSql('DROP INDEX RecurringGLInvoices_fk_Account ON RecurringGLInvoices');

        $this->addSql('ALTER TABLE RecurringGLInvoices CHANGE RecurringID RecurringID BIGINT UNSIGNED NOT NULL, CHANGE Account Account INT UNSIGNED NOT NULL, CHANGE Amount Amount NUMERIC(16, 4) NOT NULL');

        $this->addSql('ALTER TABLE RecurringInvoices DROP FOREIGN KEY RecurringInvoices_fk_SupplierNo');
        $this->addSql('DROP INDEX RecurringID ON RecurringInvoices');
        $this->addSql('DROP INDEX RecurringInvoices_fk_SupplierNo ON RecurringInvoices');

        $this->addSql('ALTER TABLE RecurringInvoices CHANGE SupplierNo SupplierNo BIGINT UNSIGNED NOT NULL, CHANGE SuppReference SuppReference VARCHAR(36) NOT NULL, CHANGE Dates Dates VARCHAR(30) NOT NULL');

        $this->addSql("
            update SuppTrans st
            set st.RecurringTransID = NULL
            where st.RecurringTransID not in (
                select RecurringID from RecurringInvoices
            )
        ");


        $this->addSql('ALTER TABLE RecurringGLInvoices ADD CONSTRAINT FK_500D5D2092916C0C FOREIGN KEY (RecurringID) REFERENCES RecurringInvoices (RecurringID)');
        $this->addSql('ALTER TABLE RecurringGLInvoices ADD CONSTRAINT FK_500D5D20B28B6F38 FOREIGN KEY (Account) REFERENCES ChartMaster (AccountCode)');
        $this->addSql('ALTER TABLE RecurringInvoices ADD CONSTRAINT FK_11440BCD48CBED4C FOREIGN KEY (SupplierNo) REFERENCES Suppliers (SupplierID)');

        $this->addSql('ALTER TABLE SuppTrans ADD CONSTRAINT FK_D9EEEDA760B80847 FOREIGN KEY (RecurringTransID) REFERENCES RecurringInvoices (RecurringID) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
