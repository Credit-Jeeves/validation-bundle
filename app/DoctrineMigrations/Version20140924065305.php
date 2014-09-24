<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140924065305 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE jms_job_related_entities
                ADD report_id BIGINT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE jms_job_related_entities
                ADD CONSTRAINT FK_E956F4E24BD2A4C0
                FOREIGN KEY (report_id)
                REFERENCES cj_applicant_report (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_E956F4E24BD2A4C0 ON jms_job_related_entities (report_id)"
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
                FOREIGN KEY FK_E956F4E24BD2A4C0"
        );
        $this->addSql(
            "DROP INDEX IDX_E956F4E24BD2A4C0 ON jms_job_related_entities"
        );
        $this->addSql(
            "ALTER TABLE jms_job_related_entities
                DROP report_id"
        );
    }
}
