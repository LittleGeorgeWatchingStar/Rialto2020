<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Clean up supplier indexes and add 'search_url' attributes.
 */
class Version20160513162617 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("set session sql_mode = ''");

        $this->addSql('DROP INDEX SupplierID_2 ON Suppliers');
        $this->addSql('DROP INDEX SupplierID ON Suppliers');
        $this->addSql('DROP INDEX CurrCode ON Suppliers');
        $this->addSql('DROP INDEX PaymentTerms ON Suppliers');
        $this->addSql('DROP INDEX TaxAuthority ON Suppliers');
        $this->addSql('DROP INDEX SuppName ON Suppliers');

        $this->addSql('CREATE INDEX IDX_2ED93BFF2D7E37EA ON Suppliers (SuppName)');

        $this->addSql("
INSERT INTO SupplierAttribute (supplierID, attribute, value) VALUES
(1, 'search_url', 'https://www.arrow.com/en/products/search?q=:q'),
(3, 'search_url', 'https://www.digikey.com/catalog/en/search?expanded=true&searchText=:q'),
(31, 'search_url', 'https://products.avnet.com/shop/SearchDisplay?sType=SimpleSearch&resultCatEntryType=2&searchSource=Q&searchType=100&avnSearchType=pro&searchTerm=:q');
        ");

        $this->addSql("
        DELETE FROM SupplierAttribute WHERE attribute = 'api_service'
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("DELETE FROM SupplierAttribute WHERE attribute = 'search_url'");
    }
}
