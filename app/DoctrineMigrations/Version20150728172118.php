<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150728172118 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE cj_operation
                CHANGE type type
                enum('charge','other','rent','report','custom') COLLATE utf8_unicode_ci NOT NULL
                COMMENT '(DC2Type:OperationType)'"
        );
        $this->addSql(
            "ALTER TABLE rj_deposit_account
                ADD type ENUM('application_fee','security_deposit','rent')
                    COMMENT '(DC2Type:DepositAccountType)' DEFAULT 'rent' NOT NULL"
        );
        $this->addSql(
            "CREATE UNIQUE INDEX da_unique_constraint ON rj_deposit_account (type,
                group_id,
                payment_processor)"
        );
        $this->addSql(
            "ALTER TABLE rj_deposit_account
                CHANGE group_id group_id BIGINT NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_contract
                CHANGE group_id group_id BIGINT NOT NULL"
        );
        $this->addSql("
            ALTER TABLE rj_deposit_account DROP FOREIGN KEY FK_7F2B897FE54D947;
            ALTER TABLE rj_deposit_account DROP KEY UNIQ_7F2B897FE54D947;
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE cj_operation
                CHANGE type type
                enum('charge','other','rent','report') COLLATE utf8_unicode_ci NOT NULL
                COMMENT '(DC2Type:OperationType)'"
        );
        $this->addSql(
            "ALTER TABLE rj_deposit_account
                DROP type"
        );
        $this->addSql(
            "DROP INDEX da_unique_constraint ON rj_deposit_account"
        );
        $this->addSql(
            "ALTER TABLE rj_deposit_account
                CHANGE group_id group_id BIGINT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_contract
                CHANGE group_id group_id BIGINT DEFAULT NULL"
        );
    }
}
