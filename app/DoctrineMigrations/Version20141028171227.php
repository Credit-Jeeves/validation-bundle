<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20141028171227 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_user
                CHANGE type type ENUM('admin','applicant','dealer','tenant','tenant','landlord','partner')
                    COMMENT '(DC2Type:UserType)' NOT NULL"
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
                CHANGE type type ENUM('admin','applicant','dealer','tenant','tenant','landlord')
                    COMMENT '(DC2Type:UserType)' NOT NULL"
        );
    }
}
