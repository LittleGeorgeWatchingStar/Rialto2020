<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add shelving tables.
 */
class Version20170111192712 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Stock_Rack (id INT UNSIGNED AUTO_INCREMENT NOT NULL, facility VARCHAR(5) NOT NULL, name VARCHAR(20) NOT NULL, esdProtection TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_CBA262825E237E06 (name), INDEX IDX_CBA26282105994B2 (facility), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Stock_ShelfPosition (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, bin BIGINT UNSIGNED DEFAULT NULL, shelf INT UNSIGNED NOT NULL, x INT UNSIGNED NOT NULL, y INT UNSIGNED NOT NULL, z INT UNSIGNED NOT NULL, UNIQUE INDEX UNIQ_2828245BAA275AED (bin), INDEX IDX_2828245BA5475BE3 (shelf), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Stock_Shelf (id INT UNSIGNED AUTO_INCREMENT NOT NULL, rack INT UNSIGNED DEFAULT NULL, indexNo INT UNSIGNED NOT NULL, velocity VARCHAR(20) NOT NULL, INDEX IDX_7E0AE7C13DD796A8 (rack), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Stock_Shelf_BinStyle (shelf INT UNSIGNED NOT NULL, binStyle VARCHAR(20) NOT NULL, INDEX IDX_8B25507AA5475BE3 (shelf), INDEX IDX_8B25507AEF627FC0 (binStyle), PRIMARY KEY(shelf, binStyle)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Stock_Rack ADD CONSTRAINT FK_CBA26282105994B2 FOREIGN KEY (facility) REFERENCES Locations (LocCode) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE Stock_ShelfPosition ADD CONSTRAINT FK_2828245BAA275AED FOREIGN KEY (bin) REFERENCES StockSerialItems (SerialNo) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE Stock_ShelfPosition ADD CONSTRAINT FK_2828245BA5475BE3 FOREIGN KEY (shelf) REFERENCES Stock_Shelf (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Stock_Shelf ADD CONSTRAINT FK_7E0AE7C13DD796A8 FOREIGN KEY (rack) REFERENCES Stock_Rack (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Stock_Shelf_BinStyle ADD CONSTRAINT FK_8B25507AA5475BE3 FOREIGN KEY (shelf) REFERENCES Stock_Shelf (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Stock_Shelf_BinStyle ADD CONSTRAINT FK_8B25507AEF627FC0 FOREIGN KEY (binStyle) REFERENCES Stock_BinStyle (id) ON DELETE RESTRICT');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Stock_Shelf DROP FOREIGN KEY FK_7E0AE7C13DD796A8');
        $this->addSql('ALTER TABLE Stock_ShelfPosition DROP FOREIGN KEY FK_2828245BA5475BE3');
        $this->addSql('ALTER TABLE Stock_Shelf_BinStyle DROP FOREIGN KEY FK_8B25507AA5475BE3');
        $this->addSql('DROP TABLE Stock_Rack');
        $this->addSql('DROP TABLE Stock_ShelfPosition');
        $this->addSql('DROP TABLE Stock_Shelf');
        $this->addSql('DROP TABLE Stock_Shelf_BinStyle');
    }
}
