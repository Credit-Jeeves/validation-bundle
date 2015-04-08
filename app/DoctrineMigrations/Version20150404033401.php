<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150404033401 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_aci_collect_pay_contract_billing (id INT AUTO_INCREMENT NOT NULL,
                contract_id BIGINT DEFAULT NULL,
                created_at DATETIME NOT NULL,
                UNIQUE INDEX UNIQ_7E5A20572576E0FD (contract_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE rj_aci_collect_pay_user_profile (id INT AUTO_INCREMENT NOT NULL,
                user_id BIGINT DEFAULT NULL,
                profile_id INT NOT NULL,
                created_at DATETIME NOT NULL,
                UNIQUE INDEX UNIQ_E474F81BA76ED395 (user_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE rj_aci_collect_pay_settings (id INT AUTO_INCREMENT NOT NULL,
                group_id BIGINT DEFAULT NULL,
                business_id INT NOT NULL,
                UNIQUE INDEX UNIQ_50CD37FCFE54D947 (group_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_aci_collect_pay_contract_billing
                ADD CONSTRAINT FK_7E5A20572576E0FD
                FOREIGN KEY (contract_id)
                REFERENCES rj_contract (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_aci_collect_pay_user_profile
                ADD CONSTRAINT FK_E474F81BA76ED395
                FOREIGN KEY (user_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_aci_collect_pay_settings
                ADD CONSTRAINT FK_50CD37FCFE54D947
                FOREIGN KEY (group_id)
                REFERENCES cj_account_group (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account
                ADD payment_processor ENUM('heartland','aci_collect_pay')
                    COMMENT '(DC2Type:PaymentProcessor)' DEFAULT 'heartland' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_group_settings
                ADD payment_processor ENUM('heartland','aci_collect_pay')
                    COMMENT '(DC2Type:PaymentProcessor)' DEFAULT 'heartland' NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "DROP TABLE rj_aci_collect_pay_contract_billing"
        );
        $this->addSql(
            "DROP TABLE rj_aci_collect_pay_user_profile"
        );
        $this->addSql(
            "DROP TABLE rj_aci_collect_pay_settings"
        );
        $this->addSql(
            "ALTER TABLE rj_group_settings
                DROP payment_processor"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account
                DROP payment_processor"
        );
    }
}
