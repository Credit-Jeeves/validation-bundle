<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150922142023 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "UPDATE cj_order o
                INNER JOIN cj_operation op on (op.order_id = o.id)
                INNER JOIN rj_contract c on (c.id = op.contract_id)
                INNER JOIN rj_group g on (c.group_id = g.id)
                INNER JOIN rj_deposit_account da on (
                    da.group_id = g.id and da.type = 'rent' and da.payment_processor = o.payment_processor
                )
            SET o.deposit_account_id = da.id"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
    }
}
