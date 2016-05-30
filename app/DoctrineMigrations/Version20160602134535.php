<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160602134535 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_import_lease
                ADD group_id BIGINT DEFAULT NULL,
                DROP external_account_id"
        );
        $this->addSql(
            "ALTER TABLE rj_import_lease
                ADD CONSTRAINT FK_795EC218FE54D947
                FOREIGN KEY (group_id)
                REFERENCES rj_group (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_795EC218FE54D947 ON rj_import_lease (group_id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_import_lease
                ADD external_account_id VARCHAR(255) DEFAULT NULL,
                DROP group_id"
        );
    }
}
