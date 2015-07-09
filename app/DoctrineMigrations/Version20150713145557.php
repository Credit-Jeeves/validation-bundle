<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150713145557 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP TABLE session"
        );
        $this->addSql(
            "ALTER TABLE cj_user
                ADD external_landlord_id VARCHAR(255) DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_group
                ADD external_group_id VARCHAR(255) DEFAULT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_user
                DROP external_landlord_id"
        );
        $this->addSql(
            "ALTER TABLE rj_group
                DROP external_group_id"
        );
    }
}
