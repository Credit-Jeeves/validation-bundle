<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130930133044 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_payment
                CHANGE payment_account_id payment_account_id BIGINT NOT NULL,
                CHANGE contract_id contract_id BIGINT NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account
                CHANGE user_id user_id BIGINT NOT NULL"
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
            "ALTER TABLE rj_payment
                CHANGE contract_id contract_id BIGINT DEFAULT NULL,
                CHANGE payment_account_id payment_account_id BIGINT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account
                CHANGE user_id user_id BIGINT DEFAULT NULL"
        );
    }
}
