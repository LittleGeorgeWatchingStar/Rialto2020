<?php declare(strict_types=1);

namespace Rialto\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201127003856_addSupplierInternalRole extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("INSERT INTO Role (name, label, groupName) VALUES ('ROLE_SUPPLIER_INTERNAL', 'Supplier (internal)', 'Supplier')");
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("DELETE FROM Role WHERE name = 'ROLE_SUPPLIER_INTERNAL'");
    }
}
