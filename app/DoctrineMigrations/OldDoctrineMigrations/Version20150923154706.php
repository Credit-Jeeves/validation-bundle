<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150923154706 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_aci_collect_pay_contract_billing
                DROP INDEX UNIQ_7E5A20572576E0FD,
                ADD INDEX IDX_7E5A20572576E0FD (contract_id)"
        );
        $this->addSql(
            "CREATE UNIQUE INDEX billing_account_unique_constraint
                ON rj_aci_collect_pay_contract_billing (contract_id, division_id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_aci_collect_pay_contract_billing
                DROP INDEX IDX_7E5A20572576E0FD,
                ADD UNIQUE INDEX UNIQ_7E5A20572576E0FD (contract_id)"
        );
        $this->addSql(
            "DROP INDEX billing_account_unique_constraint ON rj_aci_collect_pay_contract_billing"
        );
    }
}
