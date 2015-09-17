<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150901130007 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_deposit_account
                ADD holding_id BIGINT DEFAULT NULL"
        );

        $this->addSql(
            "ALTER TABLE rj_deposit_account CHANGE account_number account_number VARCHAR(255) DEFAULT NULL"
        );

        $this->addSql(
            "CREATE UNIQUE INDEX unique_constraint_account_number ON rj_deposit_account (type,
                holding_id,
                account_number,
                payment_processor)"
        );

        $this->addSql(
            "UPDATE
                rj_deposit_account
            INNER JOIN rj_group ON rj_group.id=rj_deposit_account.group_id
            SET rj_deposit_account.holding_id = rj_group.holding_id"
        );

        $this->addSql(
            "UPDATE
                rj_deposit_account
            INNER JOIN rj_group ON rj_group.id=rj_deposit_account.group_id
            INNER JOIN cj_holding ON cj_holding.id = rj_group.holding_id
            INNER JOIN rj_group_account_mapping ON rj_group_account_mapping.group_id=rj_group.id
            INNER JOIN  rj_group_settings ON rj_group_settings.group_id=rj_group.id
            SET
              rj_deposit_account.account_number = rj_group_account_mapping.account_number
            WHERE rj_group_settings.payment_processor=rj_deposit_account.payment_processor
            AND rj_deposit_account.type='rent';"
        );

        $this->addSql(
            "ALTER TABLE rj_deposit_account MODIFY COLUMN holding_id bigint NOT NULL;"
        );
        $this->addSql(
            "ALTER TABLE rj_deposit_account
                ADD CONSTRAINT FK_7F2B8976CD5FBA3
                FOREIGN KEY (holding_id)
                REFERENCES cj_holding (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_7F2B8976CD5FBA3 ON rj_deposit_account (holding_id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP INDEX unique_constraint_account_number ON rj_deposit_account"
        );

        $this->addSql(
            "ALTER TABLE rj_deposit_account
                DROP
                FOREIGN KEY FK_7F2B8976CD5FBA3"
        );
        $this->addSql(
            "DROP INDEX IDX_7F2B8976CD5FBA3 ON rj_deposit_account"
        );
        $this->addSql(
            "ALTER TABLE rj_deposit_account
                DROP holding_id"
        );
        $this->addSql(
            "ALTER TABLE rj_deposit_account CHANGE account_number account_number INT(11) DEFAULT NULL"
        );
    }
}
