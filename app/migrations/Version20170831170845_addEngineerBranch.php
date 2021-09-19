<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add SalesReturn.engineerBranch column.
 */
class Version20170831170845_addEngineerBranch extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE SalesReturn ADD engineerBranch BIGINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE SalesReturn ADD CONSTRAINT FK_5AD2E8C46D4D4328 FOREIGN KEY (engineerBranch) REFERENCES CustBranch (id)');

        $michaelLew = 24663;
        $this->addSql("
            update SalesReturn rma
            join SalesReturnItem item on item.salesReturn = rma.id
            set rma.engineerBranch = $michaelLew
            where (item.passDisposition = 'engineering' or item.failDisposition = 'engineering')
            and rma.engineerBranch is null
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX IDX_5AD2E8C46D4D4328 ON SalesReturn');
        $this->addSql('ALTER TABLE SalesReturn DROP FOREIGN KEY FK_5AD2E8C46D4D4328');
        $this->addSql('ALTER TABLE SalesReturn DROP engineerBranch');
    }
}
