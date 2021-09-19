<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Populate PurchOrders.deliveryAddressId.
 */
class Version20151001103854 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            update PurchOrders po
            join Geography_Address ad on (
                po.Addr1 like ad.street1
                and po.Addr2 like ad.street2
                and po.MailStop like ad.mailStop
                and po.City like ad.city
                and po.State like ad.stateCode
                and po.Zip like ad.postalCode
                and po.Country like ad.countryCode
            )
            set po.deliveryAddressId = ad.id
        ");

        $this->addSql("
            insert into Geography_Address
            (street1
            , street2
            , mailStop
            , city
            , stateCode
            , postalCode
            , countryCode)
            select distinct
                po.Addr1
                , po.Addr2
                , po.MailStop
                , po.City
                , po.State
                , po.Zip
                , po.Country
            from PurchOrders po
            where po.deliveryAddressId is null
        ");

        $this->addSql("
            update PurchOrders po
            join Geography_Address ad on (
                po.Addr1 like ad.street1
                and po.Addr2 like ad.street2
                and po.MailStop like ad.mailStop
                and po.City like ad.city
                and po.State like ad.stateCode
                and po.Zip like ad.postalCode
                and po.Country like ad.countryCode
            )
            set po.deliveryAddressId = ad.id
            where po.deliveryAddressId is null
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
