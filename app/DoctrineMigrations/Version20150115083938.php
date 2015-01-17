<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150115083938 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE accounting_settings (id BIGINT AUTO_INCREMENT NOT NULL,
                holding_id BIGINT NOT NULL,
                api_integration ENUM('yardi voyager','resman')
                    COMMENT '(DC2Type:ApiIntegrationType)' NOT NULL,
                UNIQUE INDEX UNIQ_5DC18D376CD5FBA3 (holding_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE accounting_settings
                ADD CONSTRAINT FK_5DC18D376CD5FBA3
                FOREIGN KEY (holding_id)
                REFERENCES cj_holding (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP TABLE accounting_settings"
        );
    }
}
