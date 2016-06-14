<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160525182351 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_group
                DROP street_address_1,
                DROP street_address_2,
                DROP city,
                DROP state,
                DROP zip,
                DROP mailing_address_name,
                DROP external_group_id"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_group
                ADD street_address_1 VARCHAR(255) DEFAULT NULL,
                ADD street_address_2 VARCHAR(255) DEFAULT NULL,
                ADD city VARCHAR(255) DEFAULT NULL,
                ADD state VARCHAR(7) DEFAULT NULL,
                ADD zip VARCHAR(15) DEFAULT NULL,
                ADD mailing_address_name VARCHAR(255) DEFAULT NULL,
                ADD external_group_id VARCHAR(255) DEFAULT NULL"
        );
    }
}
