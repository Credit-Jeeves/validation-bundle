<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151103142021 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_import_api_mapping
                ADD street VARCHAR(255) DEFAULT NULL,
                ADD city VARCHAR(255)  DEFAULT NULL,
                ADD state VARCHAR(255)  DEFAULT NULL,
                ADD zip VARCHAR(15)  DEFAULT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_import_api_mapping
                DROP street,
                DROP city,
                DROP state,
                DROP zip"
        );
    }
}
