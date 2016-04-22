<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151001120032 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE rj_group_settings
                ADD allowed_debit_fee TINYINT(1) DEFAULT '0' NOT NULL,
                ADD type_debit_fee ENUM('flat_fee','percentage')
                    COMMENT '(DC2Type:TypeDebitFee)' DEFAULT 'percentage' NOT NULL,
                ADD debit_fee NUMERIC(10,
                2) DEFAULT NULL"
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
                DROP allowed_debit_fee,
                DROP type_debit_fee,
                DROP debit_fee"
        );
    }
}
