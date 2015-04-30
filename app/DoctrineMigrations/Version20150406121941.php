<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150406121941 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_deposit_account
                ADD mid INT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_deposit_account
                ADD is_passed_ach TINYINT(1) NOT NULL"
        );

        $this->addSql("
            UPDATE rj_deposit_account
            SET feeCC = 2.95
            WHERE feeCC is NULL;

            UPDATE rj_deposit_account
            SET feeACH = 0
            WHERE feeACH is NULL;

            UPDATE rj_deposit_account
            SET is_passed_ach = 1
            WHERE feeACH != 0;
        ");
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE rj_deposit_account
                DROP mid"
        );
        $this->addSql(
            "ALTER TABLE rj_deposit_account
                DROP is_passed_ach"
        );
    }
}
