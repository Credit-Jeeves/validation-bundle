<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160125172416 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_order
                CHANGE payment_processor payment_processor enum('heartland','aci','profit_stars')
                COMMENT '(DC2Type:PaymentProcessor)' NOT NULL DEFAULT 'heartland'"
        );
        $this->addSql(
            "ALTER TABLE rj_group_settings
                CHANGE payment_processor payment_processor enum('heartland','aci','profit_stars')
                COMMENT '(DC2Type:PaymentProcessor)' NOT NULL DEFAULT 'heartland'"
        );
        $this->addSql(
            "ALTER TABLE rj_deposit_account
                CHANGE payment_processor payment_processor enum('heartland','aci','profit_stars')
                COMMENT '(DC2Type:PaymentProcessor)' NOT NULL DEFAULT 'heartland'"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account
                CHANGE payment_processor payment_processor enum('heartland','aci','profit_stars')
                COMMENT '(DC2Type:PaymentProcessor)' NOT NULL DEFAULT 'heartland'"
        );
        $this->addSql(
            "ALTER TABLE rj_billing_account
                CHANGE payment_processor payment_processor enum('heartland','aci','profit_stars')
                COMMENT '(DC2Type:PaymentProcessor)' NOT NULL DEFAULT 'heartland'"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_order
                CHANGE payment_processor payment_processor enum('heartland','aci')
                COMMENT '(DC2Type:PaymentProcessor)' NOT NULL DEFAULT 'heartland'"
        );
        $this->addSql(
            "ALTER TABLE rj_group_settings
                CHANGE payment_processor payment_processor enum('heartland','aci')
                COMMENT '(DC2Type:PaymentProcessor)' NOT NULL DEFAULT 'heartland'"
        );
        $this->addSql(
            "ALTER TABLE rj_deposit_account
                CHANGE payment_processor payment_processor enum('heartland','aci')
                COMMENT '(DC2Type:PaymentProcessor)' NOT NULL DEFAULT 'heartland'"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account
                CHANGE payment_processor payment_processor enum('heartland','aci')
                COMMENT '(DC2Type:PaymentProcessor)' NOT NULL DEFAULT 'heartland'"
        );
        $this->addSql(
            "ALTER TABLE rj_billing_account
                CHANGE payment_processor payment_processor enum('heartland','aci')
                COMMENT '(DC2Type:PaymentProcessor)' NOT NULL DEFAULT 'heartland'"
        );
    }
}
