<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add fields to Role: numeric ID, label, groupName.
 */
class Version20161101104212 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE UserRole DROP FOREIGN KEY UserRole_fk_roleId');
        $this->addSql('ALTER TABLE UserRole DROP FOREIGN KEY UserRole_fk_userId');
        $this->addSql('ALTER TABLE UserRole DROP FOREIGN KEY FK_A8503F7364B64DCC');
        $this->addSql('DROP INDEX UserRole_fk_roleId ON UserRole');

        $this->addSql("ALTER TABLE UserRole DROP PRIMARY KEY");
        $this->addSql('ALTER TABLE UserRole CHANGE userId userId VARCHAR(20) NOT NULL, CHANGE roleId roleName VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE UserRole ADD roleId INT UNSIGNED NULL');

        $this->addSql('ALTER TABLE Role DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE Role CHANGE id name VARCHAR(50) NOT NULL, ADD `label` VARCHAR(50) NOT NULL, ADD groupName VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE Role ADD id INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY FIRST');

        $this->addSql("
            UPDATE UserRole ur
            JOIN Role r ON ur.roleName = r.name
            SET ur.roleId = r.id
        ");

        $this->addSql("ALTER TABLE UserRole DROP roleName");
        $this->addSql('ALTER TABLE UserRole CHANGE roleId roleId INT UNSIGNED NOT NULL');

        $this->addSql('ALTER TABLE UserRole ADD PRIMARY KEY (userId, roleId)');
        $this->addSql('ALTER TABLE UserRole ADD CONSTRAINT FK_A8503F73B8C2FD88 FOREIGN KEY (roleId) REFERENCES Role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE UserRole ADD CONSTRAINT FK_A8503F7364B64DCC FOREIGN KEY (userId) REFERENCES WWW_Users (UserID) ON DELETE CASCADE');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_F75B25545E237E06 ON Role (name)');


        $this->addSql("
            UPDATE Role SET name = 'ROLE_SUPPLIER_ADVANCED' WHERE name = 'ROLE_SUPPLIER' 
        ");
        $this->addSql("DELETE FROM Role WHERE name = 'ROLE_SUPPLIER_DASHBOARD'");
        $this->addSql("
            UPDATE Role SET label = lower(replace(replace(name, 'ROLE_', ''), '_', ' '))
        ");
        $this->addSql("
            INSERT INTO Role 
            (name, label, groupName) VALUES 
            ('ROLE_SUPPLIER_SIMPLE', 'Supplier (simple)', 'Supplier')
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
