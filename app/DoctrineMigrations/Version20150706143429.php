<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150706143429 extends AbstractMigration
{
    /**
     * @TODO move data from
     */
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_group_settings
                ADD feeCC NUMERIC(10,
                2) DEFAULT NULL,
                ADD feeACH NUMERIC(10,
                2) DEFAULT NULL,
                ADD is_passed_ach TINYINT(1) NOT NULL"
        );

        $this->addSql(
            "UPDATE rj_group_settings as s
             INNER JOIN rj_group as g ON g.id = s.group_id
             INNER JOIN rj_deposit_account as d ON d.group_id=g.id
             SET s.feeCC=d.feeCC,s.feeACH=d.feeACH,s.is_passed_ach=d.is_passed_ach"
        );

        $this->addSql(
            "ALTER TABLE rj_deposit_account
                ADD payment_processor ENUM('heartland','aci')
                    COMMENT '(DC2Type:PaymentProcessor)' NOT NULL,
                DROP feeCC,
                DROP feeACH,
                DROP is_passed_ach"
        );

        $this->addSql(
            "ALTER TABLE rj_billing_account
                ADD payment_processor ENUM('heartland','aci')
                    COMMENT '(DC2Type:PaymentProcessor)' NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_billing_account
                DROP payment_processor"
        );
        $this->addSql(
            "ALTER TABLE rj_deposit_account
                ADD feeCC NUMERIC(10,
                2) DEFAULT NULL,
                ADD feeACH NUMERIC(10,
                2) DEFAULT NULL,
                ADD is_passed_ach TINYINT(1) NOT NULL,
                DROP payment_processor"
        );
        $this->addSql(
            "ALTER TABLE rj_group_settings
                DROP feeCC,
                DROP feeACH,
                DROP is_passed_ach"
        );
    }
}
