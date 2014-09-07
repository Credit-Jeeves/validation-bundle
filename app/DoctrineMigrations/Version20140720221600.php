<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140720221600 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE jms_job_related_entities
                ADD credit_track_payment_account_id BIGINT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE jms_job_related_entities
                ADD CONSTRAINT FK_E956F4E29305140F
                FOREIGN KEY (credit_track_payment_account_id)
                REFERENCES rj_payment_account (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_E956F4E29305140F ON jms_job_related_entities (credit_track_payment_account_id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE jms_job_related_entities
                DROP
                FOREIGN KEY FK_E956F4E29305140F"
        );
        $this->addSql(
            "DROP INDEX IDX_E956F4E29305140F ON jms_job_related_entities"
        );
        $this->addSql(
            "ALTER TABLE jms_job_related_entities
                DROP credit_track_payment_account_id"
        );
    }
}
