<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150128090834 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE accounting_settings CHANGE api_integration api_integration
             ENUM(  'none',  'yardi voyager',  'resman' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci
             NOT NULL COMMENT  '(DC2Type:ApiIntegrationType)';"
        );

    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE accounting_settings CHANGE api_integration api_integration
             ENUM('yardi voyager',  'resman') CHARACTER SET utf8 COLLATE utf8_unicode_ci
             NOT NULL COMMENT  '(DC2Type:ApiIntegrationType)';"
        );
    }
}
