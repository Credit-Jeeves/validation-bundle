<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130930152538 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_payment_account
                ADD group_id BIGINT NOT NULL"
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

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
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
}
