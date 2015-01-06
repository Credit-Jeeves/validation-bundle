<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150106133416 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        // Move account numbers from rj_deposit_account table to group_account_mapping table
        $this->addSql('
            insert into group_account_mapping (holding_id, group_id, account_number)
            SELECT g.holding_id, da.group_id, da.account_number
            FROM rj_deposit_account da
            inner join cj_account_group g on da.group_id = g.id
            where account_number is not null'
        );
        $this->addSql(
            "DROP INDEX UNIQ_7F2B897B1A4D127 ON rj_deposit_account"
        );
        $this->addSql(
            "ALTER TABLE rj_deposit_account
                DROP account_number"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_deposit_account
                ADD account_number INT DEFAULT NULL"
        );
        $this->addSql(
            "CREATE UNIQUE INDEX UNIQ_7F2B897B1A4D127 ON rj_deposit_account (account_number)"
        );
    }
}
