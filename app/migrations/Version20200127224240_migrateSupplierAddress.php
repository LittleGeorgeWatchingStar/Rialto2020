<?php declare(strict_types=1);

namespace Rialto\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200127224240_migrateSupplierAddress extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("ALTER TABLE Suppliers MODIFY COLUMN SupplierSince DATE DEFAULT '1970-01-01'");
        $this->addSql('ALTER TABLE Suppliers ADD orderAddressID BIGINT UNSIGNED NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE Suppliers ADD paymentAddressID BIGINT UNSIGNED NULL DEFAULT NULL');
        $this->addSql('INSERT INTO Geography_Address (street1, street2, mailStop, city, stateCode, postalCode, countryCode) 
                            SELECT DISTINCT OrderAddr1, OrderAddr2, OrderMailStop, OrderCity, OrderState, OrderZip, OrderCountry FROM Suppliers
                            UNION 
                            SELECT DISTINCT PaymentAddr1, PaymentAddr2, PaymentMailStop, PaymentCity, PaymentState, PaymentZip, PaymentCountry FROM Suppliers');
        $this->addSql('UPDATE Suppliers suppliers JOIN Geography_Address address 
                            ON  suppliers.OrderAddr1 = address.street1
                            AND suppliers.OrderAddr2  = address.street2
                            AND suppliers.OrderMailStop = address.mailStop
                            AND suppliers.OrderCity = address.city
                            AND suppliers.OrderState = address.stateCode
                            AND suppliers.OrderZip = address.postalCode
                            AND suppliers.OrderCountry = address.countryCode
                            SET suppliers.orderAddressID = address.id;');
        $this->addSql('UPDATE Suppliers suppliers JOIN Geography_Address address 
                            ON  suppliers.PaymentAddr1 = address.street1
                            AND suppliers.PaymentAddr2  = address.street2
                            AND suppliers.PaymentMailStop = address.mailStop
                            AND suppliers.PaymentCity = address.city
                            AND suppliers.PaymentState = address.stateCode
                            AND suppliers.PaymentZip = address.postalCode
                            AND suppliers.PaymentCountry = address.countryCode
                            SET suppliers.paymentAddressID = address.id;');
        $this->addSql('ALTER TABLE Suppliers MODIFY COLUMN orderAddressID BIGINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE Suppliers MODIFY COLUMN paymentAddressID BIGINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE Suppliers
                            ADD CONSTRAINT Suppliers_fk_orderAddressID
                            FOREIGN KEY (orderAddressID) REFERENCES Geography_Address (id)');
        $this->addSql('ALTER TABLE Suppliers
                            ADD CONSTRAINT Suppliers_fk_paymentAddressID
                            FOREIGN KEY (paymentAddressID) REFERENCES Geography_Address (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->throwIrreversibleMigrationException();
    }
}
