<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160121125316 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_holding
            CHANGE COLUMN `api_integration_type` `accounting_system`
            enum('none','yardi voyager','resman','mri','amsi', 'boston post')
            COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:AccountingSystem)'"
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
            "ALTER TABLE cj_holding
            CHANGE COLUMN  `accounting_system` `api_integration_type`
            enum('none','yardi voyager','resman','mri','amsi')
            COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:ApiIntegrationType)'"
        );
    }
}
