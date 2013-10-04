<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130930125630 extends AbstractMigration
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
                ADD payment_account_id BIGINT NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_payment
                ADD CONSTRAINT FK_A4398CF0AE9DDE6F
                FOREIGN KEY (payment_account_id)
                REFERENCES rj_payment_account (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_A4398CF0AE9DDE6F ON rj_payment (payment_account_id)"
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
                DROP
                FOREIGN KEY FK_A4398CF0AE9DDE6F"
        );
        $this->addSql(
            "DROP INDEX IDX_A4398CF0AE9DDE6F ON rj_payment"
        );
        $this->addSql(
            "ALTER TABLE rj_payment
                DROP payment_account_id"
        );
    }
}
