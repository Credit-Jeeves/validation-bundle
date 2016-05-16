<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160215122837 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "CREATE TABLE rj_payment_history (id BIGINT AUTO_INCREMENT NOT NULL,
                object_id BIGINT DEFAULT NULL,
                contract_id BIGINT DEFAULT NULL,
                payment_account_id BIGINT DEFAULT NULL,
                deposit_account_id BIGINT DEFAULT NULL,
                type ENUM('recurring','one_time','immediate')
                    COMMENT '(DC2Type:PaymentType)' NOT NULL,
                status ENUM('active','close','flagged')
                    COMMENT '(DC2Type:PaymentStatus)' NOT NULL,
                amount NUMERIC(10,
                2) DEFAULT NULL,
                total NUMERIC(10,
                2) NOT NULL,
                paid_for DATE DEFAULT NULL,
                due_date INT NOT NULL,
                start_month INT NOT NULL,
                start_year INT NOT NULL,
                end_month INT DEFAULT NULL,
                end_year INT DEFAULT NULL,
                updated_at DATETIME NOT NULL,
                close_details LONGTEXT DEFAULT NULL
                    COMMENT '(DC2Type:array)',
                action VARCHAR(8) NOT NULL,
                logged_at DATETIME NOT NULL,
                INDEX IDX_45FD6AC9232D562B (object_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_history
                ADD CONSTRAINT FK_45FD6AC9232D562B
                FOREIGN KEY (object_id)
                REFERENCES rj_payment (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "DROP TABLE rj_payment_history"
        );
    }
}
