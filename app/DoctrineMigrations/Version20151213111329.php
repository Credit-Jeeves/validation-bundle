<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151213111329 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "DROP TABLE rj_aci_collect_pay_contract_billing"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "CREATE TABLE rj_aci_collect_pay_contract_billing (id INT AUTO_INCREMENT NOT NULL,
                contract_id BIGINT DEFAULT NULL,
                division_id VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL,
                UNIQUE INDEX billing_account_unique_constraint (contract_id,
                division_id),
                INDEX IDX_7E5A20572576E0FD (contract_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
    }
}
