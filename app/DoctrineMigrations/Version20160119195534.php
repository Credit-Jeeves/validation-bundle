<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160119195534 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_contract
                ADD payment_allowed TINYINT(1) DEFAULT '1' NOT NULL"
        );

        $this->addSql(
            "ALTER TABLE rj_contract_history
                ADD payment_allowed TINYINT(1) DEFAULT '1' NOT NULL"
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
                DROP payment_allowed"
        );

        $this->addSql(
            "ALTER TABLE rj_contract_history
                DROP payment_allowed"
        );
    }
}
