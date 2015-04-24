<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150424131312 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_holding
                ADD api_integration_type ENUM('none','yardi voyager','resman','mri','amsi')
                    COMMENT '(DC2Type:ApiIntegrationType)' DEFAULT 'none' NOT NULL"
        );

        $this->addSql(
            "UPDATE cj_holding h
               INNER JOIN accounting_settings ac ON ac.holding_id = h.id
               SET h.api_integration_type = ac.api_integration"
        );

        $this->addSql(
            "DROP TABLE accounting_settings"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE cj_holding
                DROP apiIntegrationType"
        );

    }
}
