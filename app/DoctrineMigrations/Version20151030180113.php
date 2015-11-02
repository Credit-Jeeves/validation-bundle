<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @todo: add new table
 */
class Version20151030180113 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_property
                CHANGE country country VARCHAR(3) DEFAULT NULL,
                CHANGE city city VARCHAR(255) DEFAULT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_property
                CHANGE country country VARCHAR(3) NOT NULL,
                CHANGE city city VARCHAR(255) NOT NULL"
        );

    }
}
