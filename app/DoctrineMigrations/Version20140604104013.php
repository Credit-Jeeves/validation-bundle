<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140604104013 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_resident_mapping
                DROP first_name,
                DROP last_name"
        );
        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                ADD first_name VARCHAR(255) NOT NULL,
                ADD last_name VARCHAR(255) NOT NULL"
        );

    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                DROP first_name,
                DROP last_name"
        );
        $this->addSql(
            "ALTER TABLE rj_resident_mapping
                ADD first_name VARCHAR(255) NOT NULL,
                ADD last_name VARCHAR(255) NOT NULL"
        );
    }
}
