<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151002141231 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "insert into rj_aci_collect_pay_profile_billing(profile_id,division_id,created_at)
            select up.id, cb.division_id, now()
            from rj_contract c inner join rj_aci_collect_pay_contract_billing cb on (c.id = cb.contract_id)
            inner join rj_aci_collect_pay_user_profile up on (c.tenant_id = up.user_id)
            group by up.id, cb.division_id
            order by up.id"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "delete from rj_aci_collect_pay_profile_billing"
        );
    }
}
