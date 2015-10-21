<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151009125950 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_property
                ADD ss_lat VARCHAR(255) DEFAULT NULL,
                ADD ss_long VARCHAR(255) DEFAULT NULL,
                ADD ss_index VARCHAR(255) DEFAULT NULL"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_property
                DROP ss_lat,
                DROP ss_long,
                DROP ss_index"
        );
    }
}
