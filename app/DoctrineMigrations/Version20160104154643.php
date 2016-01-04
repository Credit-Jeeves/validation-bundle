<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160104154643 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_import_property
                CHANGE address_has_units address_has_units TINYINT(1) DEFAULT '1' NOT NULL"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_import_property
                CHANGE address_has_units address_has_units TINYINT(1) DEFAULT '0' NOT NULL"
        );
    }
}
