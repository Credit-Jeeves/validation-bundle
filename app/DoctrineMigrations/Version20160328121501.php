<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160328121501 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_trusted_landlord_jira_mapping (
                id BIGINT AUTO_INCREMENT NOT NULL,
                trusted_landlord_id BIGINT NOT NULL,
                jira_key VARCHAR(255) NOT NULL,
                UNIQUE INDEX UNIQ_FB76C67A5A545F5B (trusted_landlord_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE rj_check_mailing_address (
                id BIGINT AUTO_INCREMENT NOT NULL,
                addressee VARCHAR(255) NOT NULL,
                state VARCHAR(2) NOT NULL,
                city VARCHAR(255) NOT NULL,
                address1 VARCHAR(255) NOT NULL,
                address2 VARCHAR(255) NOT NULL,
                zip VARCHAR(15) NOT NULL,
                ss_index VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE INDEX UNIQ_318A22EE21F4A992 (ss_index),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE rj_trusted_landlord (
                id BIGINT AUTO_INCREMENT NOT NULL,
                check_mailing_address_id BIGINT NOT NULL,
                first_name VARCHAR(255) DEFAULT NULL,
                last_name VARCHAR(255) DEFAULT NULL,
                company_name VARCHAR(255) DEFAULT NULL,
                type ENUM('person','company')
                    COMMENT '(DC2Type:TrustedLandlordType)' NOT NULL,
                phone VARCHAR(255) DEFAULT NULL,
                status ENUM('new','trusted','rfi','denied', 'in progress', 'waiting for info')
                    COMMENT '(DC2Type:TrustedLandlordStatus)' NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE INDEX UNIQ_819A682741728E87 (check_mailing_address_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_trusted_landlord_jira_mapping
                ADD CONSTRAINT FK_FB76C67A5A545F5B
                FOREIGN KEY (trusted_landlord_id)
                REFERENCES rj_trusted_landlord (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_trusted_landlord
                ADD CONSTRAINT FK_819A682741728E87
                FOREIGN KEY (check_mailing_address_id)
                REFERENCES rj_check_mailing_address (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_group
                ADD trusted_landlord_id BIGINT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_group
                ADD CONSTRAINT FK_F2AB53D55A545F5B
                FOREIGN KEY (trusted_landlord_id)
                REFERENCES rj_trusted_landlord (id)"
        );
        $this->addSql(
            "CREATE UNIQUE INDEX UNIQ_F2AB53D55A545F5B ON rj_group (trusted_landlord_id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE rj_trusted_landlord
                DROP
                FOREIGN KEY FK_819A682741728E87"
        );
        $this->addSql(
            "ALTER TABLE rj_group
                DROP
                FOREIGN KEY FK_F2AB53D55A545F5B"
        );
        $this->addSql(
            "ALTER TABLE rj_trusted_landlord_jira_mapping
                DROP
                FOREIGN KEY FK_FB76C67A5A545F5B"
        );
        $this->addSql(
            "DROP TABLE rj_trusted_landlord_jira_mapping"
        );
        $this->addSql(
            "DROP TABLE rj_check_mailing_address"
        );
        $this->addSql(
            "DROP TABLE rj_trusted_landlord"
        );
        $this->addSql(
            "DROP INDEX UNIQ_F2AB53D55A545F5B ON rj_group"
        );
        $this->addSql(
            "ALTER TABLE rj_group
                DROP trusted_landlord_id"
        );
    }
}
