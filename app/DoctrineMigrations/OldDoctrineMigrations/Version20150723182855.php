<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150723182855 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_group_settings
                CHANGE payment_processor payment_processor ENUM('heartland','aci','aci_collect_pay')
                    COMMENT '(DC2Type:PaymentProcessor)' NOT NULL DEFAULT 'heartland'"
        );

        $this->addSql("
            UPDATE rj_group_settings
            SET payment_processor='aci'
            WHERE payment_processor='aci_collect_pay';"
        );

        $this->addSql(
            "ALTER TABLE rj_group_settings
                CHANGE payment_processor payment_processor ENUM('heartland','aci')
                    COMMENT '(DC2Type:PaymentProcessor)' NOT NULL DEFAULT 'heartland'"
        );
    }

    public function down(Schema $schema)
    {
    }
}
