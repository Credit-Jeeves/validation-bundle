<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150706143421 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_deposit_account
                DROP feeCC,
                DROP feeACH,
                DROP is_passed_ach"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_deposit_account
                ADD feeCC NUMERIC(10,
                2) DEFAULT NULL,
                ADD feeACH NUMERIC(10,
                2) DEFAULT NULL,
                ADD is_passed_ach TINYINT(1) NOT NULL"
        );

    }
}
