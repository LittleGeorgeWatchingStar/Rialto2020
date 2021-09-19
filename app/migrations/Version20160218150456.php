<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Widen AuthorizationCode to 20 characters.
 *
 * @see http://app.payment.authorize.net/e/es.aspx?s=986383348&e=1092320&elq=015c7527040a4172bb47cb8a732b8b5f&elqaid=483&elqat=1&elqTrackId=b4d9a2a0f1424fe28f720ff9d8ae4d54
 */
class Version20160218150456 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE CardTrans CHANGE AuthorizationCode AuthorizationCode VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE CardTrans CHANGE AuthorizationCode AuthorizationCode VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci');
    }
}
