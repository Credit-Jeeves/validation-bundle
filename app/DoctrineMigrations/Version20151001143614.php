<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151001143614 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_aci_collect_pay_profile_billing (
                id INT AUTO_INCREMENT NOT NULL,
                profile_id INT DEFAULT NULL,
                division_id VARCHAR(255) DEFAULT NULL,
                billing_account_number VARCHAR(20) NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX IDX_A93E11B7CCFA12B8 (profile_id),
                UNIQUE INDEX profile_billing_unique_constraint (profile_id, division_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_aci_collect_pay_profile_billing
                ADD CONSTRAINT FK_A93E11B7CCFA12B8
                FOREIGN KEY (profile_id)
                REFERENCES rj_aci_collect_pay_user_profile (id)"
        );

        $this->addSql(
            "ALTER TABLE rj_aci_collect_pay_group_profile
                ADD billing_account_number VARCHAR(20) NOT NULL"
        );

        $this->addSql(
            "insert into rj_aci_collect_pay_profile_billing(profile_id,division_id,billing_account_number,created_at)
            select up.id, cb.division_id, c.id, now()
            from rj_contract c inner join rj_aci_collect_pay_contract_billing cb on (c.id = cb.contract_id)
            inner join rj_aci_collect_pay_user_profile up on (c.tenant_id = up.user_id)
            group by up.id, cb.division_id
            order by up.id"
        );

        $this->addSql(
            "update rj_aci_collect_pay_group_profile
            set billing_account_number = group_id"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP TABLE rj_aci_collect_pay_profile_billing"
        );

        $this->addSql(
            "ALTER TABLE rj_aci_collect_pay_group_profile
                DROP billing_account_number"
        );
    }
}
