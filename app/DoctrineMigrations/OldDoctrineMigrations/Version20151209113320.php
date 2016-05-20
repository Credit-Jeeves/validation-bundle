<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151209113320 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "CREATE UNIQUE INDEX UNIQ_1791C28721F4A992 ON rj_property_address (ss_index)"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "DROP INDEX UNIQ_1791C28721F4A992 ON rj_property_address"
        );
    }
}
