<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140305112209 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_contract
                ADD balance NUMERIC(10,
                2) DEFAULT '0.00' NOT NULL,
                ADD imported_balance NUMERIC(10,
                2) DEFAULT '0.00' NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_contract
                DROP balance,
                DROP imported_balance"
        );
    }
}
