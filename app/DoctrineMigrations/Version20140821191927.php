<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140821191927 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_checkout_heartland
                ADD payment_account_id BIGINT DEFAULT NULL AFTER order_id"
        );
        $this->addSql(
            "ALTER TABLE rj_checkout_heartland
                ADD CONSTRAINT FK_A1CC4699AE9DDE6F
                FOREIGN KEY (payment_account_id)
                REFERENCES rj_payment_account (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_A1CC4699AE9DDE6F ON rj_checkout_heartland (payment_account_id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_checkout_heartland
                DROP
                FOREIGN KEY FK_A1CC4699AE9DDE6F"
        );
        $this->addSql(
            "DROP INDEX IDX_A1CC4699AE9DDE6F ON rj_checkout_heartland"
        );
        $this->addSql(
            "ALTER TABLE rj_checkout_heartland
                DROP payment_account_id"
        );
    }
}
