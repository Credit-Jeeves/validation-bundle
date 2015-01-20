<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150120174015 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE rj_payment
             CHANGE COLUMN `status` `status` ENUM('active', 'close')"
        );
        $this->addSql(
            "ALTER TABLE rj_payment
             ADD close_details LONGTEXT DEFAULT NULL"
        );
    }
    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE rj_payment
             CHANGE COLUMN `status` `status` ENUM('active', 'close', 'pause')"
        );
        $this->addSql(
            "ALTER TABLE rj_payment
             DROP close_details"
        );
    }
} 
