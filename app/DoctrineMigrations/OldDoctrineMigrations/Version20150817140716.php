<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150817140716 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE rj_contract
                DROP balance"
        );
        $this->addSql(
            "ALTER TABLE rj_contract_history
                DROP balance"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE rj_contract ADD balance NUMERIC(10,2) DEFAULT '0.00' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_contract_history ADD balance NUMERIC(10,2) DEFAULT '0.00' NOT NULL"
        );
    }
}
