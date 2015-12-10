<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151209113319 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_group_settings
                ADD is_external_resident_follows_unit TINYINT(1) DEFAULT '0' NOT NULL"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_group_settings
                DROP is_external_resident_follows_unit"
        );
    }
}
