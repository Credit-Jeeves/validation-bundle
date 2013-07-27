<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130702012807 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
//         $this->addSql(
//             "CREATE TABLE rj_property (
//                 id BIGINT AUTO_INCREMENT NOT NULL,
//                 country VARCHAR(3) NOT NULL,
//                 area VARCHAR(255) DEFAULT NULL,
//                 city VARCHAR(255) NOT NULL,
//                 district VARCHAR(255) DEFAULT NULL,
//                 street_address VARCHAR(255) NOT NULL,
//                 street_number VARCHAR(255) DEFAULT NULL,
//                 zip VARCHAR(15) NOT NULL,
//                 jb FLOAT DEFAULT NULL,
//                 kb FLOAT DEFAULT NULL,
//                 created_at DATETIME NOT NULL,
//                 updated_at DATETIME NOT NULL,
//                 PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
//         );
        $this->addSql(
            "CREATE INDEX log_class_lookup_idx ON ext_log_entries (object_class)"
        );
        $this->addSql(
            "CREATE INDEX log_date_lookup_idx ON ext_log_entries (logged_at)"
        );
        $this->addSql(
            "CREATE INDEX log_user_lookup_idx ON ext_log_entries (username)"
        );
        $this->addSql(
            "CREATE INDEX log_version_lookup_idx ON ext_log_entries (object_id,
                object_class,
                version)"
        );
        $this->addSql(
            "ALTER TABLE email_translation
                DROP
                FOREIGN KEY email_translation_translatable_id_email_id"
        );
        $this->addSql(
            "ALTER TABLE email_translation
                CHANGE id id INT AUTO_INCREMENT NOT NULL,
                CHANGE translatable_id translatable_id INT DEFAULT NULL,
                CHANGE locale locale VARCHAR(8) NOT NULL,
                CHANGE property property VARCHAR(32) NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE email
                CHANGE id id INT AUTO_INCREMENT NOT NULL,
                CHANGE name name VARCHAR(255) NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE email_translation
                ADD CONSTRAINT FK_A2A939D82C2AC5D3
                FOREIGN KEY (translatable_id)
                REFERENCES email (id)"
        );
        $this->addSql(
            "CREATE INDEX lookup_idx ON email_translation (locale,
                translatable_id)"
        );
        $this->addSql(
            "CREATE UNIQUE INDEX lookup_unique_idx ON email_translation (locale,
                translatable_id,
                property)"
        );
        $this->addSql(
            "ALTER TABLE sent_email
                CHANGE id id INT AUTO_INCREMENT NOT NULL,
                CHANGE uniqueid uniqueId VARCHAR(255) NOT NULL,
                CHANGE fromemails fromEmails LONGTEXT NOT NULL
                    COMMENT '(DC2Type:array)',
                CHANGE toemails toEmails LONGTEXT NOT NULL
                    COMMENT '(DC2Type:array)',
                CHANGE source source LONGTEXT NOT NULL,
                CHANGE contenttype contentType VARCHAR(255) NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_incentives
                DROP
                FOREIGN KEY ccci_1"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_incentives
                DROP
                FOREIGN KEY cj_applicant_incentives_cj_applicant_id_cj_user_id"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_incentives
                DROP
                FOREIGN KEY cj_applicant_incentives_cj_incentive_id_cj_group_incentives_id"
        );
        $this->addSql(
            "DROP INDEX cj_applicant_incentives_cj_tradeline_id_idx ON cj_applicant_incentives"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_incentives
                ADD CONSTRAINT FK_61F54ABB1846CDE5
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_incentives
                ADD CONSTRAINT FK_61F54ABB7E2A1DEB
                FOREIGN KEY (cj_incentive_id)
                REFERENCES cj_group_incentives (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group_affiliate
                DROP
                FOREIGN KEY ccci"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group_affiliate
                DROP
                FOREIGN KEY cj_account_group_affiliate_cj_account_id_cj_user_id"
        );
        $this->addSql(
            "DROP INDEX cj_account_group_affiliate_cj_account_id_idx ON cj_account_group_affiliate"
        );
        $this->addSql(
            "DROP INDEX cj_account_group_affiliate_cj_account_group_id_idx ON cj_account_group_affiliate"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group_affiliate
                CHANGE culture culture ENUM('en','hi','test','es')
                    COMMENT '(DC2Type:UserCulture)' DEFAULT 'en' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_vehicle
                DROP
                FOREIGN KEY cj_vehicle_cj_applicant_id_cj_user_id"
        );
        $this->addSql(
            "ALTER TABLE cj_vehicle
                DROP INDEX cj_vehicle_cj_applicant_id_idx/*,
                ADD UNIQUE INDEX UNIQ_1AFD06AD1846CDE5 (cj_applicant_id)*/"
        );
        $this->addSql(
            "ALTER TABLE cj_vehicle
                ADD CONSTRAINT FK_1AFD06AD1846CDE5
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_lead
                DROP
                FOREIGN KEY cj_lead_cj_account_id_cj_user_id"
        );
        $this->addSql(
            "ALTER TABLE cj_lead
                DROP
                FOREIGN KEY cj_lead_cj_applicant_id_cj_user_id"
        );
        $this->addSql(
            "ALTER TABLE cj_lead
                DROP
                FOREIGN KEY cj_lead_cj_group_id_cj_account_group_id"
        );
        $this->addSql(
            "ALTER TABLE cj_lead
                CHANGE fraction fraction SMALLINT DEFAULT '0',
                CHANGE status status ENUM('new','prequal','active','idle','ready','finished','expired','processed')
                    COMMENT '(DC2Type:LeadStatus)' DEFAULT 'new' NOT NULL,
                CHANGE source source ENUM('office','webpage')
                    COMMENT '(DC2Type:LeadSource)' DEFAULT 'office'"
        );
        $this->addSql(
            "ALTER TABLE cj_lead
                ADD CONSTRAINT FK_3DCB43F71846CDE5
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_lead
                ADD CONSTRAINT FK_3DCB43F7ED8F6A55
                FOREIGN KEY (cj_account_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_lead
                ADD CONSTRAINT FK_3DCB43F752E95DE5
                FOREIGN KEY (cj_group_id)
                REFERENCES cj_account_group (id)"
        );
        $this->addSql(
            "DROP INDEX email ON cj_user"
        );
//        $this->addSql(
//            "DROP INDEX cj_user_type_idx ON cj_user"
//        );
        $this->addSql(
            "ALTER TABLE cj_user
                DROP plain_password,
                CHANGE street_address1 street_address1 LONGTEXT DEFAULT NULL,
                CHANGE street_address2 street_address2 LONGTEXT DEFAULT NULL,
                CHANGE ssn ssn LONGTEXT DEFAULT NULL,
                CHANGE password password VARCHAR(255) NOT NULL,
                CHANGE culture culture ENUM('en','hi','test','es')
                    COMMENT '(DC2Type:UserCulture)' DEFAULT 'en' NOT NULL,
                CHANGE is_verified is_verified ENUM('none','failed','locked','passed')
                    COMMENT '(DC2Type:UserIsVerified)' DEFAULT 'none' NOT NULL,
                CHANGE type type ENUM('applicant','admin','dealer','tenant','landlord')
                    COMMENT '(DC2Type:UserType)' NOT NULL,
                CHANGE enabled enabled TINYINT(1) NOT NULL,
                CHANGE salt salt VARCHAR(255) NOT NULL,
                CHANGE locked locked TINYINT(1) NOT NULL,
                CHANGE expired expired TINYINT(1) NOT NULL,
                CHANGE roles roles LONGTEXT NOT NULL
                    COMMENT '(DC2Type:array)',
                CHANGE credentials_expired credentials_expired TINYINT(1) NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_dealer_group
                DROP
                FOREIGN KEY cj_dealer_group_dealer_id_cj_user_id"
        );
        $this->addSql(
            "ALTER TABLE cj_dealer_group
                DROP
                FOREIGN KEY cj_dealer_group_group_id_cj_account_group_id_1"
        );
        $this->addSql(
            "ALTER TABLE cj_dealer_group
                DROP PRIMARY KEY"
        );
        $this->addSql(
            "ALTER TABLE cj_dealer_group
                CHANGE group_id group_id BIGINT NOT NULL,
                CHANGE dealer_id dealer_id BIGINT NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_dealer_group
                ADD CONSTRAINT FK_CFE38D5F249E6EA1
                FOREIGN KEY (dealer_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_dealer_group
                ADD CONSTRAINT FK_CFE38D5FFE54D947
                FOREIGN KEY (group_id)
                REFERENCES cj_account_group (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_dealer_group
                ADD PRIMARY KEY (dealer_id,
                group_id)"
        );
        $this->addSql(
            "DROP INDEX cj_login_defense_type_idx ON cj_login_defense"
        );
        $this->addSql(
            "ALTER TABLE atb_simulation
                DROP
                FOREIGN KEY atb_simulation_cj_applicant_report_id_cj_applicant_report_id"
        );
        $this->addSql(
            "ALTER TABLE atb_simulation
                CHANGE type type ENUM('score','cash','search')
                    COMMENT '(DC2Type:AtbType)' DEFAULT 'score' NOT NULL,
                CHANGE score_current score_current LONGTEXT NOT NULL,
                CHANGE result result LONGTEXT NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE atb_simulation
                ADD CONSTRAINT FK_BD5BF4F22A26A0ED
                FOREIGN KEY (cj_applicant_report_id)
                REFERENCES cj_applicant_report (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_report
                DROP
                FOREIGN KEY cj_applicant_report_cj_applicant_id_cj_user_id"
        );
        $this->addSql(
            "DROP INDEX cj_applicant_report_type_idx ON cj_applicant_report"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_report
                CHANGE raw_data raw_data LONGTEXT NOT NULL,
                CHANGE type type ENUM('d2c','prequal')
                    COMMENT '(DC2Type:ReportType)' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_report
                ADD CONSTRAINT FK_DA7942E81846CDE5
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_purchase
                DROP
                FOREIGN KEY cj_purchase_cj_account_id_cj_user_id"
        );
        $this->addSql(
            "ALTER TABLE cj_purchase
                DROP
                FOREIGN KEY cj_purchase_cj_lead_id_cj_lead_id"
        );
        $this->addSql(
            "DROP INDEX cj_purchase_cj_account_id_idx ON cj_purchase"
        );
        $this->addSql(
            "DROP INDEX cj_purchase_cj_lead_id_idx ON cj_purchase"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_tradelines
                DROP
                FOREIGN KEY cj_applicant_tradelines_cj_applicant_id_cj_user_id"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_tradelines
                DROP
                FOREIGN KEY cj_applicant_tradelines_cj_group_id_cj_account_group_id"
        );
        $this->addSql(
            "DROP INDEX cj_applicant_tradelines_cj_group_id_idx	 ON cj_applicant_tradelines"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_tradelines
                ADD CONSTRAINT FK_356123071846CDE5
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_lead_history
                DROP
                FOREIGN KEY cj_lead_history_editor_id_cj_user_id"
        );
        $this->addSql(
            "ALTER TABLE cj_lead_history
                DROP
                FOREIGN KEY cj_lead_history_ibfk_1"
        );
        $this->addSql(
            "DROP INDEX editor_id_idx ON cj_lead_history"
        );
        $this->addSql(
            "ALTER TABLE cj_lead_history
                CHANGE fraction fraction SMALLINT DEFAULT '0',
                CHANGE status status ENUM('new','prequal','active','idle','ready','finished','expired','processed')
                    COMMENT '(DC2Type:LeadStatus)' DEFAULT 'new' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_lead_history
                ADD CONSTRAINT FK_F12171C1232D562B
                FOREIGN KEY (object_id)
                REFERENCES cj_lead (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_order
                DROP
                FOREIGN KEY cj_order_cj_applicant_id_cj_user_id"
        );
        $this->addSql(
            "ALTER TABLE cj_order
                CHANGE status status ENUM('new','complete','error','cancelled')
                    COMMENT '(DC2Type:OrderStatus)' DEFAULT 'new' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_order
                ADD CONSTRAINT FK_DA53B53D1846CDE5
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_order_operation
                DROP
                FOREIGN KEY cj_order_operation_cj_operation_id_cj_operation_id"
        );
        $this->addSql(
            "ALTER TABLE cj_order_operation
                DROP
                FOREIGN KEY cj_order_operation_cj_order_id_cj_order_id"
        );
//        $this->addSql(
//            "ALTER TABLE cj_order_operation
//                DROP PRIMARY KEY"
//        );
        $this->addSql(
            "ALTER TABLE cj_order_operation
                DROP id"
        );
        $this->addSql(
            "ALTER TABLE cj_order_operation
                ADD CONSTRAINT FK_1FF923042122E99A
                FOREIGN KEY (cj_order_id)
                REFERENCES cj_order (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_order_operation
                ADD CONSTRAINT FK_1FF92304CBF96867
                FOREIGN KEY (cj_operation_id)
                REFERENCES cj_operation (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_order_operation
                ADD PRIMARY KEY (cj_order_id,
                cj_operation_id)"
        );
        $this->addSql(
            "ALTER TABLE cj_checkout_authorize_net_aim
                DROP
                FOREIGN KEY cj_checkout_authorize_net_aim_cj_order_id_cj_order_id"
        );
        $this->addSql(
            "ALTER TABLE cj_checkout_authorize_net_aim
                ADD CONSTRAINT FK_93DCFF9B2122E99A
                FOREIGN KEY (cj_order_id)
                REFERENCES cj_order (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_group_incentives
                DROP
                FOREIGN KEY cj_group_incentives_cj_group_id_cj_account_group_id"
        );
        $this->addSql(
            "ALTER TABLE cj_group_incentives
                CHANGE text text LONGTEXT NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_group_incentives
                ADD CONSTRAINT FK_7434DF5452E95DE5
                FOREIGN KEY (cj_group_id)
                REFERENCES cj_account_group (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                DROP
                FOREIGN KEY cj_account_group_cj_affiliate_id_cj_affiliate_id"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                DROP
                FOREIGN KEY cj_account_group_dealer_id_cj_user_id"
        );
        $this->addSql(
            "DROP INDEX code ON cj_account_group"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                CHANGE fee_type fee_type ENUM('flat','lead')
                    COMMENT '(DC2Type:GroupFeeType)' DEFAULT 'flat' NOT NULL,
                CHANGE type type ENUM('vehicle','estate')
                    COMMENT '(DC2Type:GroupType)' DEFAULT 'vehicle' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                ADD CONSTRAINT FK_FCA7EE881047997E
                FOREIGN KEY (cj_affiliate_id)
                REFERENCES cj_affiliate (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                ADD CONSTRAINT FK_FCA7EE88727ACA70
                FOREIGN KEY (parent_id)
                REFERENCES cj_account_group (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                ADD CONSTRAINT FK_FCA7EE88249E6EA1
                FOREIGN KEY (dealer_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_FCA7EE88727ACA70 ON cj_account_group (parent_id)"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_pidkiq
                DROP
                FOREIGN KEY cj_applicant_pidkiq_cj_applicant_id_cj_user_id"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_pidkiq
                CHANGE questions questions LONGTEXT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_pidkiq
                ADD CONSTRAINT FK_536F59E31846CDE5
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_pricing
                DROP
                FOREIGN KEY cj_pricing_cj_account_group_id_cj_account_group_id"
        );
        $this->addSql(
            "DROP INDEX cj_pricing_cj_account_group_id_idx ON cj_pricing"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                DROP INDEX cj_operation_cj_applicant_report_id_idx,
                ADD UNIQUE INDEX UNIQ_21F5D92D2A26A0ED (cj_applicant_report_id)"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                DROP
                FOREIGN KEY cj_operation_cj_applicant_report_id_cj_applicant_report_id"
        );
        $this->addSql(
            "DROP INDEX cj_operation_type_idx ON cj_operation"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                CHANGE type type ENUM('report')
                    COMMENT '(DC2Type:OperationType)' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                ADD CONSTRAINT FK_21F5D92D2A26A0ED
                FOREIGN KEY (cj_applicant_report_id)
                REFERENCES cj_applicant_report (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_address
                DROP
                FOREIGN KEY cj_address_user_id_cj_user_id"
        );
        $this->addSql(
            "ALTER TABLE cj_address
                ADD CONSTRAINT FK_C338DAAA76ED395
                FOREIGN KEY (user_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_settings
                CHANGE pidkiq_password pidkiq_password LONGTEXT NOT NULL,
                CHANGE pidkiq_eai pidkiq_eai LONGTEXT NOT NULL,
                CHANGE net_connect_password net_connect_password LONGTEXT NOT NULL,
                CHANGE net_connect_eai net_connect_eai LONGTEXT NOT NULL,
                CHANGE contract contract LONGTEXT NOT NULL,
                CHANGE rights rights LONGTEXT NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_score
                DROP
                FOREIGN KEY cj_applicant_score_cj_applicant_id_cj_user_id"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_score
                CHANGE score score LONGTEXT NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_score
                ADD CONSTRAINT FK_655E33C31846CDE5
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id)"
        );
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
//         $this->addSql(
//             "DROP TABLE rj_property"
//         );
        $this->addSql(
            "ALTER TABLE atb_simulation
                DROP
                FOREIGN KEY FK_BD5BF4F22A26A0ED"
        );
        $this->addSql(
            "ALTER TABLE atb_simulation
                CHANGE type type VARCHAR(255) DEFAULT 'score' NOT NULL,
                CHANGE score_current score_current VARCHAR(255) NOT NULL,
                CHANGE result result LONGTEXT NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE atb_simulation
                ADD CONSTRAINT atb_simulation_cj_applicant_report_id_cj_applicant_report_id
                FOREIGN KEY (cj_applicant_report_id)
                REFERENCES cj_applicant_report (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                DROP
                FOREIGN KEY FK_FCA7EE881047997E"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                DROP
                FOREIGN KEY FK_FCA7EE88727ACA70"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                DROP
                FOREIGN KEY FK_FCA7EE88249E6EA1"
        );
        $this->addSql(
            "DROP INDEX IDX_FCA7EE88727ACA70 ON cj_account_group"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                CHANGE fee_type fee_type VARCHAR(255) DEFAULT 'flat' NOT NULL,
                CHANGE type type VARCHAR(255) DEFAULT 'vehicle' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                ADD CONSTRAINT cj_account_group_cj_affiliate_id_cj_affiliate_id
                FOREIGN KEY (cj_affiliate_id)
                REFERENCES cj_affiliate (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group
                ADD CONSTRAINT cj_account_group_dealer_id_cj_user_id
                FOREIGN KEY (dealer_id)
                REFERENCES cj_user (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "CREATE UNIQUE INDEX code ON cj_account_group (code)"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group_affiliate
                CHANGE culture culture VARCHAR(255) DEFAULT 'en' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group_affiliate
                ADD CONSTRAINT ccci
                FOREIGN KEY (cj_account_group_id)
                REFERENCES cj_account_group (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_account_group_affiliate
                ADD CONSTRAINT cj_account_group_affiliate_cj_account_id_cj_user_id
                FOREIGN KEY (cj_account_id)
                REFERENCES cj_user (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "CREATE INDEX cj_account_group_affiliate_cj_account_id_idx ON cj_account_group_affiliate (cj_account_id)"
        );
        $this->addSql(
            "CREATE INDEX cj_account_group_affiliate_cj_account_group_id_idx ON cj_account_group_affiliate (cj_account_group_id)"
        );
        $this->addSql(
            "ALTER TABLE cj_address
                DROP
                FOREIGN KEY FK_C338DAAA76ED395"
        );
        $this->addSql(
            "ALTER TABLE cj_address
                ADD CONSTRAINT cj_address_user_id_cj_user_id
                FOREIGN KEY (user_id)
                REFERENCES cj_user (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_incentives
                DROP
                FOREIGN KEY FK_61F54ABB1846CDE5"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_incentives
                DROP
                FOREIGN KEY FK_61F54ABB7E2A1DEB"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_incentives
                ADD CONSTRAINT ccci_1
                FOREIGN KEY (cj_tradeline_id)
                REFERENCES cj_applicant_tradelines (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_incentives
                ADD CONSTRAINT cj_applicant_incentives_cj_applicant_id_cj_user_id
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_incentives
                ADD CONSTRAINT cj_applicant_incentives_cj_incentive_id_cj_group_incentives_id
                FOREIGN KEY (cj_incentive_id)
                REFERENCES cj_group_incentives (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "CREATE INDEX cj_applicant_incentives_cj_tradeline_id_idx ON cj_applicant_incentives (cj_tradeline_id)"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_pidkiq
                DROP
                FOREIGN KEY FK_536F59E31846CDE5"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_pidkiq
                CHANGE questions questions LONGTEXT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_pidkiq
                ADD CONSTRAINT cj_applicant_pidkiq_cj_applicant_id_cj_user_id
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_report
                DROP
                FOREIGN KEY FK_DA7942E81846CDE5"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_report
                CHANGE raw_data raw_data LONGTEXT NOT NULL,
                CHANGE type type VARCHAR(255) NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_report
                ADD CONSTRAINT cj_applicant_report_cj_applicant_id_cj_user_id
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "CREATE INDEX cj_applicant_report_type_idx ON cj_applicant_report (type)"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_score
                DROP
                FOREIGN KEY FK_655E33C31846CDE5"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_score
                CHANGE score score VARCHAR(50) NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_score
                ADD CONSTRAINT cj_applicant_score_cj_applicant_id_cj_user_id
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_tradelines
                DROP
                FOREIGN KEY FK_356123071846CDE5"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_tradelines
                ADD CONSTRAINT cj_applicant_tradelines_cj_applicant_id_cj_user_id
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_applicant_tradelines
                ADD CONSTRAINT cj_applicant_tradelines_cj_group_id_cj_account_group_id
                FOREIGN KEY (cj_group_id)
                REFERENCES cj_account_group (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "CREATE INDEX cj_applicant_tradelines_cj_group_id_idx	 ON cj_applicant_tradelines (cj_group_id)"
        );
        $this->addSql(
            "ALTER TABLE cj_checkout_authorize_net_aim
                DROP
                FOREIGN KEY FK_93DCFF9B2122E99A"
        );
        $this->addSql(
            "ALTER TABLE cj_checkout_authorize_net_aim
                ADD CONSTRAINT cj_checkout_authorize_net_aim_cj_order_id_cj_order_id
                FOREIGN KEY (cj_order_id)
                REFERENCES cj_order (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_dealer_group
                DROP
                FOREIGN KEY FK_CFE38D5F249E6EA1"
        );
        $this->addSql(
            "ALTER TABLE cj_dealer_group
                DROP
                FOREIGN KEY FK_CFE38D5FFE54D947"
        );
        $this->addSql(
            "ALTER TABLE cj_dealer_group
                DROP PRIMARY KEY"
        );
        $this->addSql(
            "ALTER TABLE cj_dealer_group
                CHANGE dealer_id dealer_id BIGINT DEFAULT '0' NOT NULL,
                CHANGE group_id group_id BIGINT DEFAULT '0' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_dealer_group
                ADD CONSTRAINT cj_dealer_group_dealer_id_cj_user_id
                FOREIGN KEY (dealer_id)
                REFERENCES cj_user (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_dealer_group
                ADD CONSTRAINT cj_dealer_group_group_id_cj_account_group_id
                FOREIGN KEY (group_id)
                REFERENCES cj_account_group (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_dealer_group
                ADD PRIMARY KEY (group_id,
                dealer_id)"
        );
        $this->addSql(
            "ALTER TABLE cj_group_incentives
                DROP
                FOREIGN KEY FK_7434DF5452E95DE5"
        );
        $this->addSql(
            "ALTER TABLE cj_group_incentives
                CHANGE text text LONGTEXT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_group_incentives
                ADD CONSTRAINT cj_group_incentives_cj_group_id_cj_account_group_id
                FOREIGN KEY (cj_group_id)
                REFERENCES cj_account_group (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_lead
                DROP
                FOREIGN KEY FK_3DCB43F71846CDE5"
        );
        $this->addSql(
            "ALTER TABLE cj_lead
                DROP
                FOREIGN KEY FK_3DCB43F7ED8F6A55"
        );
        $this->addSql(
            "ALTER TABLE cj_lead
                DROP
                FOREIGN KEY FK_3DCB43F752E95DE5"
        );
        $this->addSql(
            "ALTER TABLE cj_lead
                CHANGE fraction fraction INT DEFAULT 0,
                CHANGE status status VARCHAR(255) DEFAULT 'new' NOT NULL,
                CHANGE source source VARCHAR(255) DEFAULT 'office'"
        );
        $this->addSql(
            "ALTER TABLE cj_lead
                ADD CONSTRAINT cj_lead_cj_account_id_cj_user_id
                FOREIGN KEY (cj_account_id)
                REFERENCES cj_user (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_lead
                ADD CONSTRAINT cj_lead_cj_applicant_id_cj_user_id
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_lead
                ADD CONSTRAINT cj_lead_cj_group_id_cj_account_group_id
                FOREIGN KEY (cj_group_id)
                REFERENCES cj_account_group (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_lead_history
                DROP
                FOREIGN KEY FK_F12171C1232D562B"
        );
        $this->addSql(
            "ALTER TABLE cj_lead_history
                CHANGE fraction fraction INT DEFAULT 0,
                CHANGE status status VARCHAR(255) DEFAULT 'new' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_lead_history
                ADD CONSTRAINT cj_lead_history_editor_id_cj_user_id
                FOREIGN KEY (editor_id)
                REFERENCES cj_user (id) ON DELETE SET NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_lead_history
                ADD CONSTRAINT cj_lead_history_ibfk_1
                FOREIGN KEY (object_id)
                REFERENCES cj_lead (id) ON UPDATE CASCADE ON DELETE CASCADE"
        );
        $this->addSql(
            "CREATE INDEX editor_id_idx ON cj_lead_history (editor_id)"
        );
        $this->addSql(
            "CREATE INDEX cj_login_defense_type_idx ON cj_login_defense (type)"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                DROP INDEX UNIQ_21F5D92D2A26A0ED,
                ADD INDEX cj_operation_cj_applicant_report_id_cj_applicant_report_id (cj_applicant_report_id)"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                DROP
                FOREIGN KEY FK_21F5D92D2A26A0ED"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                CHANGE type type VARCHAR(255) NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                ADD CONSTRAINT cj_operation_cj_applicant_report_id_cj_applicant_report_id
                FOREIGN KEY (cj_applicant_report_id)
                REFERENCES cj_applicant_report (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "CREATE INDEX cj_operation_type_idx ON cj_operation (type)"
        );
        $this->addSql(
            "ALTER TABLE cj_order
                DROP
                FOREIGN KEY FK_DA53B53D1846CDE5"
        );
        $this->addSql(
            "ALTER TABLE cj_order
                CHANGE status status VARCHAR(255) DEFAULT 'new' NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_order
                ADD CONSTRAINT cj_order_cj_applicant_id_cj_user_id
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_order_operation
                DROP
                FOREIGN KEY FK_1FF923042122E99A"
        );
        $this->addSql(
            "ALTER TABLE cj_order_operation
                DROP
                FOREIGN KEY FK_1FF92304CBF96867"
        );
        $this->addSql(
            "ALTER TABLE cj_order_operation
                DROP PRIMARY KEY"
        );
        $this->addSql(
            "ALTER TABLE  `cj_order_operation` ADD  `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST"
        );
        $this->addSql(
            "ALTER TABLE cj_order_operation
                ADD CONSTRAINT cj_order_operation_cj_operation_id_cj_operation_id
                FOREIGN KEY (cj_operation_id)
                REFERENCES cj_operation (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_order_operation
                ADD CONSTRAINT cj_order_operation_cj_order_id_cj_order_id
                FOREIGN KEY (cj_order_id)
                REFERENCES cj_order (id) ON DELETE CASCADE"
        );
//        $this->addSql(
//            "ALTER TABLE cj_order_operation
//                ADD PRIMARY KEY (id)"
//        );
        $this->addSql(
            "ALTER TABLE cj_pricing
                ADD CONSTRAINT cj_pricing_cj_account_group_id_cj_account_group_id
                FOREIGN KEY (cj_account_group_id)
                REFERENCES cj_account_group (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "CREATE INDEX cj_pricing_cj_account_group_id_idx ON cj_pricing (cj_account_group_id)"
        );
        $this->addSql(
            "ALTER TABLE cj_purchase
                ADD CONSTRAINT cj_purchase_cj_account_id_cj_user_id
                FOREIGN KEY (cj_account_id)
                REFERENCES cj_user (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE cj_purchase
                ADD CONSTRAINT cj_purchase_cj_lead_id_cj_lead_id
                FOREIGN KEY (cj_lead_id)
                REFERENCES cj_lead (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "CREATE INDEX cj_purchase_cj_account_id_idx ON cj_purchase (cj_account_id)"
        );
        $this->addSql(
            "CREATE INDEX cj_purchase_cj_lead_id_idx ON cj_purchase (cj_lead_id)"
        );
        $this->addSql(
            "ALTER TABLE cj_settings
                CHANGE pidkiq_password pidkiq_password VARCHAR(255) NOT NULL,
                CHANGE pidkiq_eai pidkiq_eai VARCHAR(255) NOT NULL,
                CHANGE net_connect_password net_connect_password VARCHAR(255) NOT NULL,
                CHANGE net_connect_eai net_connect_eai VARCHAR(255) NOT NULL,
                CHANGE contract contract LONGTEXT DEFAULT NULL,
                CHANGE rights rights LONGTEXT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_user
                ADD plain_password VARCHAR(255) DEFAULT NULL,
                CHANGE enabled enabled TINYINT(1) DEFAULT '1',
                CHANGE salt salt VARCHAR(255) DEFAULT NULL,
                CHANGE password password VARCHAR(128) DEFAULT NULL,
                CHANGE locked locked TINYINT(1) DEFAULT '0',
                CHANGE expired expired TINYINT(1) DEFAULT NULL,
                CHANGE roles roles VARCHAR(255) DEFAULT NULL,
                CHANGE credentials_expired credentials_expired TINYINT(1) DEFAULT NULL,
                CHANGE street_address1 street_address1 VARCHAR(255) DEFAULT NULL,
                CHANGE street_address2 street_address2 VARCHAR(255) DEFAULT NULL,
                CHANGE ssn ssn VARCHAR(50) DEFAULT NULL,
                CHANGE culture culture VARCHAR(255) DEFAULT 'en' NOT NULL,
                CHANGE is_verified is_verified VARCHAR(255) DEFAULT 'none' NOT NULL,
                CHANGE type type VARCHAR(255) NOT NULL"
        );
        $this->addSql(
            "CREATE UNIQUE INDEX email ON cj_user (email)"
        );
//        $this->addSql(
//            "CREATE INDEX cj_user_type_idx ON cj_user (type)"
//        );
        $this->addSql(
            "ALTER TABLE cj_vehicle
                /*DROP INDEX UNIQ_1AFD06AD1846CDE5,*/
                ADD INDEX cj_vehicle_cj_applicant_id_idx (cj_applicant_id)"
        );
        $this->addSql(
            "ALTER TABLE cj_vehicle
                DROP
                FOREIGN KEY FK_1AFD06AD1846CDE5"
        );
        $this->addSql(
            "ALTER TABLE cj_vehicle
                ADD CONSTRAINT cj_vehicle_cj_applicant_id_cj_user_id
                FOREIGN KEY (cj_applicant_id)
                REFERENCES cj_user (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE email_translation
                DROP
                FOREIGN KEY FK_A2A939D82C2AC5D3"
        );
        $this->addSql(
            "ALTER TABLE email
                CHANGE id id BIGINT AUTO_INCREMENT NOT NULL,
                CHANGE name name VARCHAR(255) DEFAULT NULL"
        );
        $this->addSql(
            "DROP INDEX lookup_idx ON email_translation"
        );
        $this->addSql(
            "DROP INDEX lookup_unique_idx ON email_translation"
        );
        $this->addSql(
            "ALTER TABLE email_translation
                CHANGE id id BIGINT AUTO_INCREMENT NOT NULL,
                CHANGE translatable_id translatable_id BIGINT DEFAULT NULL,
                CHANGE locale locale VARCHAR(8) DEFAULT NULL,
                CHANGE property property VARCHAR(32) DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE email_translation
                ADD CONSTRAINT email_translation_translatable_id_email_id
                FOREIGN KEY (translatable_id)
                REFERENCES email (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "DROP INDEX log_class_lookup_idx ON ext_log_entries"
        );
        $this->addSql(
            "DROP INDEX log_date_lookup_idx ON ext_log_entries"
        );
        $this->addSql(
            "DROP INDEX log_user_lookup_idx ON ext_log_entries"
        );
        $this->addSql(
            "DROP INDEX log_version_lookup_idx ON ext_log_entries"
        );
        $this->addSql(
            "ALTER TABLE sent_email
                CHANGE id id BIGINT AUTO_INCREMENT NOT NULL,
                CHANGE uniqueId uniqueid VARCHAR(255) DEFAULT NULL,
                CHANGE fromEmails fromemails LONGTEXT DEFAULT NULL,
                CHANGE toEmails toemails LONGTEXT DEFAULT NULL,
                CHANGE source source LONGTEXT DEFAULT NULL,
                CHANGE contentType contenttype VARCHAR(255) DEFAULT NULL"
        );
    }
}
