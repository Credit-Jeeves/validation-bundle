<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151030144017 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            update cj_order o
            inner join (
                select je.payment_id, a.order_id
                from rj_payment p
                inner join jms_job_related_entities je on p.id = je.payment_id
                inner join (
                  select distinct je.job_id, je.order_id
                  from  cj_order o inner join jms_job_related_entities je on o.id = je.order_id
                ) a on a.job_id = je.job_id
            ) j on o.id = j.order_id
            set o.payment_id = j.payment_id"
        );
    }

    public function down(Schema $schema)
    {

    }
}
