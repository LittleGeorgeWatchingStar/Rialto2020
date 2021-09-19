<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix the post date for card transactions
 */
class Version20150604113911 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        /*
        select dateCaptured, PostDate, date(dateCaptured)
            from CardTrans
            where dateCaptured is not null
            and dateCaptured >= '2015-01-01'
            and hour(dateCaptured) < 15
            and PostDate != date(dateCaptured)

        select dateCaptured, PostDate, date(date_add(dateCaptured, interval 1 day))
            from CardTrans
            where dateCaptured is not null
            and dateCaptured >= '2015-01-01'
            and hour(dateCaptured) >= 15
            and PostDate != date(date_add(dateCaptured, interval 1 day))
         */


        $this->addSql("
            update CardTrans
            set PostDate = date(dateCaptured)
            where dateCaptured is not null
            and dateCaptured >= '2015-01-01'
            and hour(dateCaptured) < 15
        ");

        $this->addSql("
            update CardTrans
            set PostDate = date(date_add(dateCaptured, interval 1 day))
            where dateCaptured is not null
            and dateCaptured >= '2015-01-01'
            and hour(dateCaptured) >= 15
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
