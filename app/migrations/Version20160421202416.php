<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Use Doctrine simple_array types for designator lists.
 */
class Version20160421202416 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX ID ON Substitutions');
        $this->addSql('ALTER TABLE Substitutions DROP FOREIGN KEY Substitutions_fk_ComponentID');
        $this->addSql('ALTER TABLE Substitutions DROP FOREIGN KEY Substitutions_fk_SubstituteID');
        $this->addSql('DROP INDEX Substitutions_fk_ComponentID ON Substitutions');
        $this->addSql('DROP INDEX Substitutions_fk_SubstituteID ON Substitutions');

        $this->addSql('ALTER TABLE Substitutions DROP ParentID, CHANGE dnpDesignators dnpDesignators LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE addDesignators addDesignators LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');

        $this->addSql('ALTER TABLE Substitutions ADD CONSTRAINT Substitutions_fk_ComponentID FOREIGN KEY (ComponentID) REFERENCES StockMaster (StockID)');
        $this->addSql('ALTER TABLE Substitutions ADD CONSTRAINT Substitutions_fk_SubstituteID FOREIGN KEY (SubstituteID) REFERENCES StockMaster (StockID)');

        $this->addSql("update Substitutions set dnpDesignators = null where dnpDesignators = ''");
        $this->addSql("update Substitutions set addDesignators = null where addDesignators = ''");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
