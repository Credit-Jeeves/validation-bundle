<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20131223092556 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE rj_contract
                CHANGE paid_to paid_to DATE DEFAULT NULL,
                CHANGE start_at start_at DATE DEFAULT NULL,
                CHANGE finish_at finish_at DATE DEFAULT NULL"
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
                CHANGE paid_to paid_to DATETIME DEFAULT NULL,
                CHANGE start_at start_at DATETIME DEFAULT NULL,
                CHANGE finish_at finish_at DATETIME DEFAULT NULL"
        );
    }
}
