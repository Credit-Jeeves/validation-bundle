<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140902110324 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE yardi_settings (id BIGINT AUTO_INCREMENT NOT NULL,
                holding_id BIGINT NOT NULL,
                url LONGTEXT NOT NULL,
                username LONGTEXT NOT NULL,
                password LONGTEXT NOT NULL,
                database_server LONGTEXT NOT NULL,
                database_name LONGTEXT NOT NULL,
                platform LONGTEXT NOT NULL,
                payment_type_ach ENUM('cash','check')
                    COMMENT '(DC2Type:PaymentTypeACH)' DEFAULT 'check' NOT NULL,
                payment_type_cc ENUM('cash','other')
                    COMMENT '(DC2Type:PaymentTypeCC)' DEFAULT 'other' NOT NULL,
                notes_ach LONGTEXT DEFAULT NULL,
                notes_cc LONGTEXT DEFAULT NULL,
                UNIQUE INDEX UNIQ_D1DAD4B36CD5FBA3 (holding_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE yardi_settings
                ADD CONSTRAINT FK_D1DAD4B36CD5FBA3
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
            "DROP TABLE yardi_settings"
        );
    }
}
