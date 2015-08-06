<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150810143427 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "CREATE TABLE rj_billing_account_migration (id VARCHAR(255) NOT NULL,
                heartland_payment_account_id INT DEFAULT NULL,
                aci_payment_account_id INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_151FCE5A31F0BB2E (heartland_payment_account_id),
                UNIQUE INDEX UNIQ_151FCE5AE60CC3C9 (aci_payment_account_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE rj_payment_account_migration (id VARCHAR(255) NOT NULL,
                heartland_payment_account_id BIGINT DEFAULT NULL,
                aci_payment_account_id BIGINT DEFAULT NULL,
                UNIQUE INDEX UNIQ_509EA7231F0BB2E (heartland_payment_account_id),
                UNIQUE INDEX UNIQ_509EA72E60CC3C9 (aci_payment_account_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_billing_account_migration
                ADD CONSTRAINT FK_151FCE5A31F0BB2E
                FOREIGN KEY (heartland_payment_account_id)
                REFERENCES rj_billing_account (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_billing_account_migration
                ADD CONSTRAINT FK_151FCE5AE60CC3C9
                FOREIGN KEY (aci_payment_account_id)
                REFERENCES rj_billing_account (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account_migration
                ADD CONSTRAINT FK_509EA7231F0BB2E
                FOREIGN KEY (heartland_payment_account_id)
                REFERENCES rj_payment_account (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account_migration
                ADD CONSTRAINT FK_509EA72E60CC3C9
                FOREIGN KEY (aci_payment_account_id)
                REFERENCES rj_payment_account (id)"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP TABLE rj_billing_account_migration"
        );
        $this->addSql(
            "DROP TABLE rj_payment_account_migration"
        );
        $this->addSql(
            "DROP INDEX unique_index ON rj_billing_account"
        );
        $this->addSql(
            "DROP INDEX unique_index ON rj_payment_account"
        );
    }
}
