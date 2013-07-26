<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130725173316 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                ADD contract_id BIGINT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                ADD CONSTRAINT FK_21F5D92D2576E0FD
                FOREIGN KEY (contract_id)
                REFERENCES rj_contract (id)"
        );
        $this->addSql(
            "CREATE UNIQUE INDEX UNIQ_21F5D92D2576E0FD ON cj_operation (contract_id)"
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
            "ALTER TABLE cj_operation
                DROP
                FOREIGN KEY FK_21F5D92D2576E0FD"
        );
        $this->addSql(
            "DROP INDEX UNIQ_21F5D92D2576E0FD ON cj_operation"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                DROP contract_id"
        );
    }
}
