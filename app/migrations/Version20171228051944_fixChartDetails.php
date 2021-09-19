<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add foreign key constraints to ChartDetails.
 */
class Version20171228051944_fixChartDetails extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("delete from ChartDetails where Period not in (select PeriodNo from Periods)");

        $this->addSql('DROP INDEX period ON ChartDetails');
        $this->addSql('ALTER TABLE ChartDetails CHANGE AccountCode AccountCode INT UNSIGNED NOT NULL, CHANGE Period Period SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE Periods CHANGE LastDate_in_Period LastDate_in_Period DATE NOT NULL');
        $this->addSql('ALTER TABLE Periods DROP INDEX LastDate_in_Period');
        $this->addSql('ALTER TABLE Periods ADD UNIQUE INDEX UNIQ_A8AAA13E19E86238 (LastDate_in_Period)');
        $this->addSql('ALTER TABLE ChartDetails ADD CONSTRAINT FK_3EA2220D5C2B50D5 FOREIGN KEY (AccountCode) REFERENCES ChartMaster (AccountCode)');
        $this->addSql('ALTER TABLE ChartDetails ADD CONSTRAINT FK_3EA2220DC2141BF8 FOREIGN KEY (Period) REFERENCES Periods (PeriodNo)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
