<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151207125109 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_property_address
                DROP jb,
                DROP kb"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_property_address
                ADD jb DOUBLE PRECISION DEFAULT NULL,
                ADD kb DOUBLE PRECISION DEFAULT NULL"
        );
    }
}
