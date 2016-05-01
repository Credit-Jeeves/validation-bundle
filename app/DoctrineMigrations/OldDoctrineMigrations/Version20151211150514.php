<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151211150514 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "DROP TABLE rj_payment_account_deposit_account"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "CREATE TABLE rj_payment_account_deposit_account (payment_account_id BIGINT NOT NULL,
                deposit_account_id INT NOT NULL,
                INDEX IDX_2E90AACFAE9DDE6F (payment_account_id),
                INDEX IDX_2E90AACF6E60BC73 (deposit_account_id),
                PRIMARY KEY(payment_account_id,
                deposit_account_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account_deposit_account
                ADD CONSTRAINT FK_2E90AACF6E60BC73
                FOREIGN KEY (deposit_account_id)
                REFERENCES rj_deposit_account (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account_deposit_account
                ADD CONSTRAINT FK_2E90AACFAE9DDE6F
                FOREIGN KEY (payment_account_id)
                REFERENCES rj_payment_account (id) ON DELETE CASCADE"
        );
    }
}
