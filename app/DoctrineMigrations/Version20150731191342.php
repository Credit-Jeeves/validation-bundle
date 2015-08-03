<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150731191342 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_transaction
                DROP
                FOREIGN KEY FK_B949C317AE9DDE6F"
        );
        $this->addSql(
            "DROP INDEX IDX_B949C317AE9DDE6F ON rj_transaction"
        );
        $this->addSql(
            "ALTER TABLE rj_transaction
                DROP payment_account_id"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_transaction
                ADD payment_account_id BIGINT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_transaction
                ADD CONSTRAINT FK_B949C317AE9DDE6F
                FOREIGN KEY (payment_account_id)
                REFERENCES rj_payment_account (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_B949C317AE9DDE6F ON rj_transaction (payment_account_id)"
        );
    }
}
