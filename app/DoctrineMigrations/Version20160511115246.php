<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160511115246 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "CREATE TABLE rj_import_lease (id BIGINT AUTO_INCREMENT NOT NULL,
                import_id BIGINT NOT NULL,
                tenant_email VARCHAR(255) DEFAULT NULL,
                first_name VARCHAR(255) DEFAULT NULL,
                last_name VARCHAR(255) DEFAULT NULL,
                phone VARCHAR(255) DEFAULT NULL,
                date_of_birth DATE DEFAULT NULL,
                external_resident_id VARCHAR(128) DEFAULT NULL,
                external_account_id VARCHAR(255) DEFAULT NULL,
                external_property_id VARCHAR(128) DEFAULT NULL,
                external_building_id VARCHAR(128) DEFAULT NULL,
                external_unit_id VARCHAR(128) NOT NULL,
                resident_status ENUM('current','past','future')
                    COMMENT '(DC2Type:ImportLeaseResidentStatus)' DEFAULT NULL,
                payment_accepted ENUM('0','1','2')
                    COMMENT '(DC2Type:PaymentAccepted)' DEFAULT NULL,
                due_date INT DEFAULT NULL,
                rent NUMERIC(10,2) DEFAULT NULL,
                integrated_balance NUMERIC(10,2) DEFAULT '0.00' NOT NULL,
                start_at DATE DEFAULT NULL,
                finish_at DATE DEFAULT NULL,
                external_lease_id VARCHAR(255) DEFAULT NULL,
                user_status ENUM('invited','not_invited','no_email','bad_email','error')
                    COMMENT '(DC2Type:ImportLeaseUserStatus)' DEFAULT NULL,
                lease_status ENUM('new','match','error')
                    COMMENT '(DC2Type:ImportLeaseStatus)' DEFAULT NULL,
                error_messages LONGTEXT DEFAULT NULL
                    COMMENT '(DC2Type:array)',
                processed TINYINT(1) DEFAULT '0' NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                INDEX IDX_795EC218B6A263D9 (import_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_import_lease
                ADD CONSTRAINT FK_795EC218B6A263D9
                FOREIGN KEY (import_id)
                REFERENCES rj_import (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "DROP TABLE rj_import_lease"
        );
    }
}
