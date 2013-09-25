<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130925051040 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_checkout_heartland (id INT AUTO_INCREMENT NOT NULL,
                order_id BIGINT DEFAULT NULL,
                messages LONGTEXT DEFAULT NULL,
                is_successful TINYINT(1) NOT NULL,
                amount NUMERIC(10,
                0) DEFAULT NULL,
                transaction_id INT DEFAULT NULL,
                merchant_name VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL,
                INDEX IDX_A1CC46998D9F6D38 (order_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE rj_payment (id BIGINT AUTO_INCREMENT NOT NULL,
                contract_id BIGINT DEFAULT NULL,
                type ENUM('recurring','one_time','immediate')
                    COMMENT '(DC2Type:PaymentType)' NOT NULL,
                status ENUM('active','pause','close')
                    COMMENT '(DC2Type:PaymentStatus)' NOT NULL,
                amount NUMERIC(10,
                0) NOT NULL,
                dueDate INT NOT NULL,
                startMonth INT NOT NULL,
                startYear INT NOT NULL,
                endMonth INT DEFAULT NULL,
                endYear INT DEFAULT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                INDEX IDX_A4398CF02576E0FD (contract_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE rj_payment_account (id BIGINT AUTO_INCREMENT NOT NULL,
                user_id BIGINT DEFAULT NULL,
                address_id BIGINT DEFAULT NULL,
                type ENUM('bank','card')
                    COMMENT '(DC2Type:PaymentAccountType)' NOT NULL,
                name VARCHAR(255) NOT NULL,
                token VARCHAR(255) NOT NULL,
                cc_expiration DATE DEFAULT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                INDEX IDX_1F714C26A76ED395 (user_id),
                INDEX IDX_1F714C26F5B7AF75 (address_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_checkout_heartland
                ADD CONSTRAINT FK_A1CC46998D9F6D38
                FOREIGN KEY (order_id)
                REFERENCES cj_order (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_payment
                ADD CONSTRAINT FK_A4398CF02576E0FD
                FOREIGN KEY (contract_id)
                REFERENCES rj_contract (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account
                ADD CONSTRAINT FK_1F714C26A76ED395
                FOREIGN KEY (user_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account
                ADD CONSTRAINT FK_1F714C26F5B7AF75
                FOREIGN KEY (address_id)
                REFERENCES cj_address (id)"
        );
        $this->addSql(
            "DROP TABLE migration_version"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group_affiliate
                ADD CONSTRAINT FK_1096A96612867DD
                FOREIGN KEY (cj_account_group_id)
                REFERENCES cj_account_group (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group_affiliate
                ADD CONSTRAINT FK_1096A966ED8F6A55
                FOREIGN KEY (cj_account_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_1096A96612867DD ON cj_account_group_affiliate (cj_account_group_id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_1096A966ED8F6A55 ON cj_account_group_affiliate (cj_account_id)"
        );
//        $this->addSql( // TODO Alex please check it
//            "ALTER TABLE cj_vehicle
//                DROP INDEX FK_1AFD06AD1846CDE5,
//                ADD UNIQUE INDEX UNIQ_1AFD06AD1846CDE5 (cj_applicant_id)"
//        );
        $this->addSql(
            "ALTER TABLE cj_applicant_tradelines
                ADD CONSTRAINT FK_3561230752E95DE5
                FOREIGN KEY (cj_group_id)
                REFERENCES cj_account_group (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_3561230752E95DE5 ON cj_applicant_tradelines (cj_group_id)"
        );
        $this->addSql(
            "ALTER TABLE cj_lead_history
                CHANGE id id BIGINT AUTO_INCREMENT NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_checkout_authorize_net_aim
                CHANGE card_code card_code VARCHAR(1) NOT NULL,
                CHANGE split_tender_id split_tender_id VARCHAR(255) DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                CHANGE type type ENUM('vehicle','estate','generic','rent')
                    COMMENT '(DC2Type:GroupType)' DEFAULT 'vehicle' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                ADD CONSTRAINT FK_FCA7EE886CD5FBA3
                FOREIGN KEY (holding_id)
                REFERENCES cj_holding (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_FCA7EE886CD5FBA3 ON cj_account_group (holding_id)"
        );
        $this->addSql(
            "ALTER TABLE rj_property
                CHANGE street street VARCHAR(255) DEFAULT NULL"
        );
        $this->addSql(
            "UPDATE cj_user SET roles='a:0:{}'"
        );
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE migration_version (version INT DEFAULT NULL) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "DROP TABLE rj_checkout_heartland"
        );
        $this->addSql(
            "DROP TABLE rj_payment"
        );
        $this->addSql(
            "DROP TABLE rj_payment_account"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                DROP
                FOREIGN KEY FK_FCA7EE886CD5FBA3"
        );
        $this->addSql(
            "DROP INDEX IDX_FCA7EE886CD5FBA3 ON cj_account_group"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                CHANGE type type VARCHAR(255) DEFAULT 'vehicle' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group_affiliate
                DROP
                FOREIGN KEY FK_1096A96612867DD"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group_affiliate
                DROP
                FOREIGN KEY FK_1096A966ED8F6A55"
        );
        $this->addSql(
            "DROP INDEX IDX_1096A96612867DD ON cj_account_group_affiliate"
        );
        $this->addSql(
            "DROP INDEX IDX_1096A966ED8F6A55 ON cj_account_group_affiliate"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_tradelines
                DROP
                FOREIGN KEY FK_3561230752E95DE5"
        );
        $this->addSql(
            "DROP INDEX IDX_3561230752E95DE5 ON cj_applicant_tradelines"
        );
        $this->addSql(
            "ALTER TABLE cj_checkout_authorize_net_aim
                CHANGE card_code card_code VARCHAR(25) NOT NULL,
                CHANGE split_tender_id split_tender_id VARCHAR(255) NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_lead_history
                CHANGE id id INT AUTO_INCREMENT NOT NULL"
        );
//        $this->addSql(// TODO Alex please check it
//            "ALTER TABLE cj_vehicle
//                DROP INDEX UNIQ_1AFD06AD1846CDE5,
//                ADD INDEX FK_1AFD06AD1846CDE5 (cj_applicant_id)"
//        );
        $this->addSql(
            "ALTER TABLE rj_property
                CHANGE street street VARCHAR(255) NOT NULL"
        );
    }
}
