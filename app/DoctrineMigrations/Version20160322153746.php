<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160322153746 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE cj_user
                DROP street_address1,
                DROP street_address2,
                DROP unit_no,
                DROP city,
                DROP state,
                DROP zip"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE cj_user
                ADD street_address1 LONGTEXT DEFAULT NULL,
                ADD street_address2 LONGTEXT DEFAULT NULL,
                ADD unit_no VARCHAR(31) DEFAULT NULL,
                ADD city VARCHAR(255) DEFAULT NULL,
                ADD state VARCHAR(7) DEFAULT NULL,
                ADD zip VARCHAR(15) DEFAULT NULL"
        );
    }
}
