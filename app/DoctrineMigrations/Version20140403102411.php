<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140403102411 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_contract
                ADD report_to_trans_union TINYINT(1) DEFAULT '0',
                ADD trans_union_start_at DATE DEFAULT NULL,
                CHANGE reporting report_to_experian TINYINT(1) DEFAULT '0',
                ADD experian_start_at DATE DEFAULT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_contract
                ADD reporting TINYINT(1) DEFAULT '0',
                DROP report_to_experian,
                DROP report_to_trans_union,
                DROP experian_start_at,
                DROP trans_union_start_at"
        );
    }
}
