<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160406091436 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_profitstars_transaction
                ADD item_id VARCHAR(255) NOT NULL,
                CHANGE transaction_number transaction_number VARCHAR(255) DEFAULT NULL"
        );
        $this->addSql(
            "CREATE UNIQUE INDEX UNIQ_FAD41AF8126F525E ON rj_profitstars_transaction (item_id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP INDEX UNIQ_FAD41AF8126F525E ON rj_profitstars_transaction"
        );
        $this->addSql(
            "ALTER TABLE rj_profitstars_transaction
                DROP item_id,
                CHANGE transaction_number transaction_number VARCHAR(255) NOT NULL"
        );
    }
}
