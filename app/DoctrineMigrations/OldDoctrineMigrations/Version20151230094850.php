<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151230094850 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_import_property
                ADD address1 VARCHAR(255) DEFAULT NULL,
                DROP street_number,
                DROP street_name"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_import_property
                ADD street_name VARCHAR(255) DEFAULT NULL,
                CHANGE address1 street_number VARCHAR(255) DEFAULT NULL"
        );
    }
}
