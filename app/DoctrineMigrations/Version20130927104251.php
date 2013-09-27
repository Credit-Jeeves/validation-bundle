<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130927104251 extends AbstractMigration
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
                CHANGE type type ENUM('report','rent')
                COMMENT '(DC2Type:OperationType)' DEFAULT 'report' NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE cj_opeartion
                CHANGE type type ENUM('report')
                COMMENT '(DC2Type:OperationType)' DEFAULT 'report' NOT NULL"
        );
    }
}
