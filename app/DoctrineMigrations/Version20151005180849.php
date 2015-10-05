<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151005180849 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "CREATE TABLE rj_debit_card_durbin (id BIGINT AUTO_INCREMENT NOT NULL,
                frb_id VARCHAR(255) DEFAULT NULL,
                short_name VARCHAR(255) DEFAULT NULL,
                city VARCHAR(255) DEFAULT NULL,
                state VARCHAR(255) DEFAULT NULL,
                type VARCHAR(255) DEFAULT NULL,
                fdic_id VARCHAR(255) DEFAULT NULL,
                ots_id VARCHAR(255) DEFAULT NULL,
                ncua_id VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP TABLE rj_debit_card_durbin"
        );
    }
}
