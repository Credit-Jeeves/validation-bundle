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

        // Move account numbers from rj_deposit_account table to rj_group_account_mapping table
        $this->addSql('
            insert into rj_group_account_mapping (holding_id, group_id, account_number)
            SELECT g.holding_id, da.group_id, da.account_number
            FROM rj_deposit_account da
            inner join cj_account_group g on da.group_id = g.id
            where account_number is not null'
        );
// NOTE: this constraint does not exist on stg or production, so dont try to remove it
//
//        $this->addSql(
//            "DROP INDEX UNIQ_7F2B897B1A4D127 ON rj_deposit_account"
//        );

// NOTE: remove this column manually after release is working in production
//
//        $this->addSql(
//            "ALTER TABLE rj_deposit_account
//                DROP account_number"
//        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );


// NOTE: putting back an empty column is not that helpful, the values will still
//       live in rj_group_account_mapping though. if reverted, remove rj_group_account_mapping table
//       manually.
//
//        $this->addSql(
//            "ALTER TABLE rj_deposit_account
//                ADD account_number INT DEFAULT NULL"
//        );

// NOTE: this constraint does not exist on stg or production, so don't put it back
//
//        $this->addSql(
//            "CREATE UNIQUE INDEX UNIQ_7F2B897B1A4D127 ON rj_deposit_account (account_number)"
//        );
    }
}
