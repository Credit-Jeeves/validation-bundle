<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140428135137 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_property
                ADD is_single TINYINT(1) DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_unit
                CHANGE property_id property_id BIGINT NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_property
                DROP is_single"
        );
        $this->addSql(
            "ALTER TABLE rj_unit
                CHANGE property_id property_id BIGINT DEFAULT NULL"
        );
    }
}
