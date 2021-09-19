<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add new product printer.
 */
class Version20170129162429_add_productPrinter extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            alter table Printer add column description VARCHAR(255) not null default '' after id
        ");
        $this->addSql("
            insert into Printer
            (id, description, host, port, printerType, pageWidth, pageHeight) VALUES 
            ('product', 'Green product labels', 'redwood.gumstix.net', 9100, 'label', 81, 252)
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
