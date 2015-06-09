<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150529123834 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_import_error
                ADD md5_row_content VARCHAR(32) NOT NULL,
                DROP row_offset"
        );
        
        $this->addSql(
            "UPDATE rj_import_error
                SET md5_row_content=md5(row_content)"
        );

        $this->addSql(
            "CREATE UNIQUE INDEX unique_index_constraint ON rj_import_error (md5_row_content,
                import_summary_id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_import_error
                ADD row_offset INT NOT NULL,
                DROP md5_row_content"
        );

        $this->addSql(
            "DROP INDEX unique_index_constraint ON rj_import_error"
        );
    }
}
