<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Get rid of WorkCentres table and related columns.
 */
class Version20160225110858 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE TurnkeyExclusions DROP WorkCentre');
        $this->addSql('DROP TABLE WorkCentres');

        $this->addSql("
            delete te from TurnkeyExclusions te
            left join StockMaster i on te.Component = i.StockID
            where i.StockID is null
        ");

        $this->addSql('ALTER TABLE TurnkeyExclusions ADD CONSTRAINT FK_93279F453A226579 FOREIGN KEY (Parent) REFERENCES StockMaster (StockID)');
        $this->addSql('ALTER TABLE TurnkeyExclusions ADD CONSTRAINT FK_93279F45CB0F23F4 FOREIGN KEY (Component) REFERENCES StockMaster (StockID)');
        $this->addSql('ALTER TABLE TurnkeyExclusions ADD CONSTRAINT FK_93279F45CF9430DB FOREIGN KEY (LocCode) REFERENCES Locations (LocCode)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE TurnkeyExclusions DROP FOREIGN KEY FK_93279F453A226579');
        $this->addSql('ALTER TABLE TurnkeyExclusions DROP FOREIGN KEY FK_93279F45CB0F23F4');
        $this->addSql('ALTER TABLE TurnkeyExclusions DROP FOREIGN KEY FK_93279F45CF9430DB');

        $this->addSql('CREATE TABLE WorkCentres (Code CHAR(5) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, Location CHAR(5) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, Description CHAR(20) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, Capacity NUMERIC(16, 4) DEFAULT \'1.0000\' NOT NULL, OverheadPerHour NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, OverheadRecoveryAct INT DEFAULT 0 NOT NULL, SetUpHrs NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, INDEX Description (Description), INDEX Location (Location), PRIMARY KEY(Code)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE TurnkeyExclusions ADD WorkCentre VARCHAR(5) NOT NULL COLLATE utf8_unicode_ci');
    }
}
