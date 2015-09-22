<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150609114347 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_account_group
                ADD mailing_address_name VARCHAR(255) DEFAULT NULL"
        );

        $this->addSql(
            "RENAME TABLE cj_account_group TO rj_group"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_account_group
                DROP mailing_address_name"
        );

        $this->addSql(
            "RENAME TABLE rj_group TO cj_account_group"
        );
    }
}
