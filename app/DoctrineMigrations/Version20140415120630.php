<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140415120630 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
          "CREATE TABLE rj_paymentaccount_depositaccount (deposit_account_id INT NOT NULL,
                payment_account_id BIGINT NOT NULL,
                INDEX IDX_3171B7F46E60BC73 (deposit_account_id),
                INDEX IDX_3171B7F4AE9DDE6F (payment_account_id),
                PRIMARY KEY(deposit_account_id,
                payment_account_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
          "ALTER TABLE rj_paymentaccount_depositaccount
                ADD CONSTRAINT FK_3171B7F46E60BC73
                FOREIGN KEY (deposit_account_id)
                REFERENCES rj_deposit_account (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_paymentaccount_depositaccount
                ADD CONSTRAINT FK_3171B7F4AE9DDE6F
                FOREIGN KEY (payment_account_id)
                REFERENCES rj_payment_account (id)"
        );
        $this->addSql(
            "INSERT INTO rj_paymentaccount_depositaccount
             SELECT rj_deposit_account.id deposit_account_id, rj_payment_account.id payment_account_id
             FROM rj_payment_account
             LEFT JOIN cj_account_group on rj_payment_account.group_id=cj_account_group.id
             LEFT JOIN rj_deposit_account on rj_deposit_account.group_id=cj_account_group.id"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account
                DROP
                FOREIGN KEY FK_1F714C26FE54D947"
        );
        $this->addSql(
            "DROP INDEX IDX_1F714C26FE54D947 ON rj_payment_account"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account
                DROP group_id"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_payment_account
                ADD group_id BIGINT NOT NULL AFTER user_id"
        );

        $this->addSql(
          "
          UPDATE rj_payment_account
                SET rj_payment_account.group_id=(
                    SELECT cj_account_group.id
                    FROM rj_paymentaccount_depositaccount
                    LEFT JOIN rj_deposit_account ON rj_deposit_account.id=rj_paymentaccount_depositaccount.deposit_account_id
                    LEFT JOIN cj_account_group ON cj_account_group.id=rj_deposit_account.group_id
                    WHERE rj_paymentaccount_depositaccount.payment_account_id=rj_payment_account.id
                )
                "
        );

        $this->addSql(
            "DROP TABLE rj_paymentaccount_depositaccount"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account
                ADD CONSTRAINT FK_1F714C26FE54D947
                FOREIGN KEY (group_id)
                REFERENCES cj_account_group (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_1F714C26FE54D947 ON rj_payment_account (group_id)"
        );
    }
}
