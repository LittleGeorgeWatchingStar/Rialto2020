<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add capture columns to CardTrans.
 */
class Version20150518100124 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE CardTrans DROP FOREIGN KEY CardTrans_fk_referenceTransactionID');
        $this->addSql('ALTER TABLE CardTrans DROP FOREIGN KEY FK_8207C1A342BEFDB2');
        $this->addSql('ALTER TABLE CardTrans ADD amountCaptured NUMERIC(12, 2) DEFAULT \'0\' NOT NULL, ADD dateCaptured DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE CardTrans ADD CONSTRAINT FK_8207C1A331EDFB77 FOREIGN KEY (referenceTransactionID) REFERENCES CardTrans (CardTransID) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8207C1A342BEFDB24AE45A29 ON CardTrans (CardID, TransactionID)');
        $this->addSql('DROP INDEX fk_8207c1a342befdb2 ON CardTrans');
        $this->addSql('CREATE INDEX IDX_8207C1A342BEFDB2 ON CardTrans (CardID)');
        $this->addSql('ALTER TABLE CardTrans ADD CONSTRAINT FK_8207C1A342BEFDB2 FOREIGN KEY (CardID) REFERENCES PaymentMethod (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE CardTrans DROP FOREIGN KEY FK_8207C1A331EDFB77');
        $this->addSql('DROP INDEX IDX_8207C1A342BEFDB24AE45A29 ON CardTrans');
        $this->addSql('ALTER TABLE CardTrans DROP FOREIGN KEY FK_8207C1A342BEFDB2');
        $this->addSql('ALTER TABLE CardTrans DROP amountCaptured, DROP dateCaptured');
        $this->addSql('ALTER TABLE CardTrans ADD CONSTRAINT CardTrans_fk_referenceTransactionID FOREIGN KEY (referenceTransactionID) REFERENCES CardTrans (CardTransID)');
        $this->addSql('DROP INDEX idx_8207c1a342befdb2 ON CardTrans');
        $this->addSql('CREATE INDEX FK_8207C1A342BEFDB2 ON CardTrans (CardID)');
        $this->addSql('ALTER TABLE CardTrans ADD CONSTRAINT FK_8207C1A342BEFDB2 FOREIGN KEY (CardID) REFERENCES PaymentMethod (id)');
    }
}
