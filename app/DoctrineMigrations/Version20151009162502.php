<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151009162502 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_hps_payment_account_merchant (id BIGINT AUTO_INCREMENT NOT NULL,
                payment_account_id BIGINT DEFAULT NULL,
                merchant_name VARCHAR(255) NOT NULL,
                INDEX IDX_63076281AE9DDE6F (payment_account_id),
                UNIQUE INDEX payment_account_hps_merchant_unique_constraint (payment_account_id,
                merchant_name),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_hps_payment_account_merchant
                ADD CONSTRAINT FK_63076281AE9DDE6F
                FOREIGN KEY (payment_account_id)
                REFERENCES rj_payment_account (id)"
        );
        $this->addSql(
            "INSERT into rj_hps_payment_account_merchant (payment_account_id,merchant_name)
            SELECT pa.id, da.merchant_name
            FROM rj_payment_account pa
            inner join rj_payment_account_deposit_account pada on pa.id = pada.payment_account_id
            inner join rj_deposit_account da on da.id = pada.deposit_account_id
            group by (concat(pa.id, da.merchant_name))"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP TABLE rj_hps_payment_account_merchant"
        );
    }
}
