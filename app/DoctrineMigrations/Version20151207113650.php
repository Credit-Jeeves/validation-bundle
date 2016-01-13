<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151207113650 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_property
                DROP country,
                DROP area,
                DROP city,
                DROP district,
                DROP street,
                DROP number,
                DROP zip,
                DROP google_reference,
                DROP jb,
                DROP kb,
                DROP is_single,
                DROP ss_lat,
                DROP ss_long,
                DROP ss_index"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_property
                ADD country VARCHAR(3) DEFAULT NULL,
                ADD area VARCHAR(255) DEFAULT NULL,
                ADD city VARCHAR(255) DEFAULT NULL,
                ADD district VARCHAR(255) DEFAULT NULL,
                ADD street VARCHAR(255) DEFAULT NULL,
                ADD number VARCHAR(255) DEFAULT NULL,
                ADD zip VARCHAR(15) DEFAULT NULL,
                ADD google_reference VARCHAR(255) DEFAULT NULL,
                ADD jb DOUBLE PRECISION DEFAULT NULL,
                ADD kb DOUBLE PRECISION DEFAULT NULL,
                ADD is_single TINYINT(1) DEFAULT NULL,
                ADD ss_lat VARCHAR(255) DEFAULT NULL,
                ADD ss_long VARCHAR(255) DEFAULT NULL,
                ADD ss_index VARCHAR(255) DEFAULT NULL"
        );
    }
}
