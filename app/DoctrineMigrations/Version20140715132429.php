<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140715132429 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE rj_group_settings
                ADD is_pay_balance_only TINYINT(1) DEFAULT '0' NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE rj_group_settings
                DROP is_pay_balance_only"
        );
    }
}
