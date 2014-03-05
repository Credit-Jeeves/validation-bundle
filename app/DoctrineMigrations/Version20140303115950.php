<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140303115950 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE cj_user
                ADD resident_id VARCHAR(128) DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_group_settings
                ADD is_integrated TINYINT(1) DEFAULT 0"
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
                DROP resident_id"
        );

        $this->addSql(
            "ALTER TABLE rj_group_settings
                DROP is_integrated"
        );
    }
}
