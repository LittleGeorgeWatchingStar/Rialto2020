<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add sweepFeesDaily
 */
class Version20150908092937 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE PaymentMethod DROP FOREIGN KEY FK_37FAAE8DD6EFA878');
        $this->addSql('DROP INDEX fk_37faae8dd6efa878 ON PaymentMethod');
        $this->addSql('ALTER TABLE PaymentMethodGroup DROP FOREIGN KEY FK_609727E69391665');
        $this->addSql('ALTER TABLE PaymentMethodGroup DROP FOREIGN KEY FK_609727E697BFFFE4');
        $this->addSql('DROP INDEX fk_609727e69391665 ON PaymentMethodGroup');
        $this->addSql('DROP INDEX fk_609727e697bfffe4 ON PaymentMethodGroup');

        $this->addSql('ALTER TABLE PaymentMethodGroup ADD sweepFeesDaily TINYINT(1) DEFAULT \'0\' NOT NULL');

        $this->addSql('CREATE INDEX IDX_37FAAE8DD6EFA878 ON PaymentMethod (groupID)');
        $this->addSql('ALTER TABLE PaymentMethod ADD CONSTRAINT FK_37FAAE8DD6EFA878 FOREIGN KEY (groupID) REFERENCES PaymentMethodGroup (id)');
        $this->addSql('CREATE INDEX IDX_609727E69391665 ON PaymentMethodGroup (depositAccountID)');
        $this->addSql('CREATE INDEX IDX_609727E697BFFFE4 ON PaymentMethodGroup (feeAccountID)');
        $this->addSql('ALTER TABLE PaymentMethodGroup ADD CONSTRAINT FK_609727E69391665 FOREIGN KEY (depositAccountID) REFERENCES ChartMaster (AccountCode)');
        $this->addSql('ALTER TABLE PaymentMethodGroup ADD CONSTRAINT FK_609727E697BFFFE4 FOREIGN KEY (feeAccountID) REFERENCES ChartMaster (AccountCode)');

        $this->addSql("update PaymentMethodGroup set sweepFeesDaily = 1 where id = 'AmEx'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE PaymentMethod DROP FOREIGN KEY FK_37FAAE8DD6EFA878');
        $this->addSql('DROP INDEX idx_37faae8dd6efa878 ON PaymentMethod');
        $this->addSql('CREATE INDEX FK_37FAAE8DD6EFA878 ON PaymentMethod (groupID)');
        $this->addSql('ALTER TABLE PaymentMethod ADD CONSTRAINT FK_37FAAE8DD6EFA878 FOREIGN KEY (groupID) REFERENCES PaymentMethodGroup (id)');
        $this->addSql('ALTER TABLE PaymentMethodGroup DROP FOREIGN KEY FK_609727E69391665');
        $this->addSql('ALTER TABLE PaymentMethodGroup DROP FOREIGN KEY FK_609727E697BFFFE4');
        $this->addSql('ALTER TABLE PaymentMethodGroup DROP sweepFeesDaily');
        $this->addSql('DROP INDEX idx_609727e69391665 ON PaymentMethodGroup');
        $this->addSql('CREATE INDEX FK_609727E69391665 ON PaymentMethodGroup (depositAccountID)');
        $this->addSql('DROP INDEX idx_609727e697bfffe4 ON PaymentMethodGroup');
        $this->addSql('CREATE INDEX FK_609727E697BFFFE4 ON PaymentMethodGroup (feeAccountID)');
        $this->addSql('ALTER TABLE PaymentMethodGroup ADD CONSTRAINT FK_609727E69391665 FOREIGN KEY (depositAccountID) REFERENCES ChartMaster (AccountCode)');
        $this->addSql('ALTER TABLE PaymentMethodGroup ADD CONSTRAINT FK_609727E697BFFFE4 FOREIGN KEY (feeAccountID) REFERENCES ChartMaster (AccountCode)');
    }
}
