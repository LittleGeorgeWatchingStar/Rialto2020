<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Populate Locations.addressId.
 */
class Version20151001114801 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            update Locations loc
            join Geography_Address ad on (
                loc.Addr1 like ad.street1
                and loc.Addr2 like ad.street2
                and loc.MailStop like ad.mailStop
                and loc.City like ad.city
                and loc.State like ad.stateCode
                and loc.Zip like ad.postalCode
                and loc.Country like ad.countryCode
            )
            set loc.addressId = ad.id
            where loc.City != ''
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
                loc.Addr1
                , loc.Addr2
                , loc.MailStop
                , loc.City
                , loc.State
                , loc.Zip
                , loc.Country
            from Locations loc
            where loc.addressId is null
            and loc.City != ''
        ");

        $this->addSql("
            update Locations loc
            join Geography_Address ad on (
                loc.Addr1 like ad.street1
                and loc.Addr2 like ad.street2
                and loc.MailStop like ad.mailStop
                and loc.City like ad.city
                and loc.State like ad.stateCode
                and loc.Zip like ad.postalCode
                and loc.Country like ad.countryCode
            )
            set loc.addressId = ad.id
            where loc.addressId is null
            and loc.City != ''
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
