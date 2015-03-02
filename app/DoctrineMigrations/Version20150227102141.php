<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150227102141 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_mri_settings (id BIGINT AUTO_INCREMENT NOT NULL,
                holding_id BIGINT NOT NULL,
                url LONGTEXT NOT NULL,
                user LONGTEXT NOT NULL,
                password LONGTEXT NOT NULL,
                database_name LONGTEXT NOT NULL,
                partner_key LONGTEXT NOT NULL,
                hash LONGTEXT NOT NULL,
                UNIQUE INDEX UNIQ_6B189E0A6CD5FBA3 (holding_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );

        $this->addSql(
            "ALTER TABLE rj_mri_settings
                ADD CONSTRAINT FK_6B189E0A6CD5FBA3
                FOREIGN KEY (holding_id)
                REFERENCES cj_holding (id)"
        );
        $this->addSql(
            "ALTER TABLE  `accounting_settings` CHANGE  `api_integration`  `api_integration`
             ENUM(  'none',  'yardi voyager',  'mri',  'resman' )
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
            "DROP TABLE mri_settings"
        );

        $this->addSql(
            "ALTER TABLE  `accounting_settings` CHANGE  `api_integration`  `api_integration`
             ENUM(  'none',  'yardi voyager',  'resman' )
             NOT NULL COMMENT  '(DC2Type:ApiIntegrationType)';"
        );
    }
}
