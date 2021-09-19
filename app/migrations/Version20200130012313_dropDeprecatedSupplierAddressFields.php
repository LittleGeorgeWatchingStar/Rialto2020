<?php declare(strict_types=1);

namespace Rialto\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200130012313_dropDeprecatedSupplierAddressFields extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Suppliers DROP OrderAddr1, DROP OrderAddr2, DROP OrderMailStop, DROP OrderCity, DROP OrderState, DROP OrderZip, DROP OrderCountry, DROP PaymentAddr1, DROP PaymentAddr2, DROP PaymentMailStop, DROP PaymentCity, DROP PaymentState, DROP PaymentZip, DROP PaymentCountry');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Suppliers ADD OrderAddr1 VARCHAR(40) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD OrderAddr2 VARCHAR(20) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD OrderMailStop VARCHAR(20) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD OrderCity VARCHAR(50) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD OrderState VARCHAR(20) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD OrderZip VARCHAR(15) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD OrderCountry VARCHAR(20) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD PaymentAddr1 VARCHAR(40) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD PaymentAddr2 VARCHAR(20) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD PaymentMailStop VARCHAR(20) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD PaymentCity VARCHAR(50) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD PaymentState VARCHAR(20) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD PaymentZip VARCHAR(15) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci, ADD PaymentCountry VARCHAR(20) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci');
    }
}
