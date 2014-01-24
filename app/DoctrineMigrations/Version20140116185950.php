<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20140116185950 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_operation
                ADD group_id BIGINT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                ADD CONSTRAINT FK_21F5D92DFE54D947
                FOREIGN KEY (group_id)
                REFERENCES cj_account_group (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_21F5D92DFE54D947 ON cj_operation (group_id)"
        );

        $this->addSql(
            "ALTER TABLE cj_operation
                CHANGE type type ENUM('report','rent','charge')
                DEFAULT 'report' NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_operation
                DROP
                FOREIGN KEY FK_21F5D92DFE54D947"
        );
        $this->addSql(
            "DROP INDEX IDX_21F5D92DFE54D947 ON cj_operation"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                DROP group_id"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                CHANGE type type ENUM('report','rent')
                DEFAULT 'report' NOT NULL"
        );
    }
}
