<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140519122143 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                CHANGE imported_balance integrated_balance NUMERIC(10,
                2) DEFAULT '0.00' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_contract_history
                CHANGE imported_balance integrated_balance NUMERIC(10,
                2) DEFAULT '0.00' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_contract
                CHANGE imported_balance integrated_balance NUMERIC(10,
                2) DEFAULT '0.00' NOT NULL"
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
                CHANGE integrated_balance imported_balance NUMERIC(10,
                2) DEFAULT '0.00' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_contract_history
                CHANGE integrated_balance imported_balance NUMERIC(10,
                2) DEFAULT '0.00' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                CHANGE integrated_balance imported_balance NUMERIC(10,
                2) DEFAULT '0.00' NOT NULL"
        );
    }
}
