<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151109204121 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "CREATE TABLE rj_import_group_settings (id BIGINT AUTO_INCREMENT NOT NULL,
                group_id BIGINT NOT NULL,
                source ENUM('csv','integrated_api')
                    COMMENT '(DC2Type:ImportSource)' DEFAULT 'csv' NOT NULL,
                import_type ENUM('single_property','multi_properties','multi_groups')
                    COMMENT '(DC2Type:ImportType)' DEFAULT 'single_property' NOT NULL,
                csv_field_delimiter VARCHAR(255) DEFAULT ',' NOT NULL,
                csv_text_delimiter VARCHAR(255) DEFAULT '\"' NOT NULL,
                csv_date_format VARCHAR(255) DEFAULT 'm/d/Y' NOT NULL,
                api_property_ids VARCHAR(255) DEFAULT NULL,
                UNIQUE INDEX UNIQ_FD9643FBFE54D947 (group_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_import_group_settings
                ADD CONSTRAINT FK_FD9643FBFE54D947
                FOREIGN KEY (group_id)
                REFERENCES rj_group (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP TABLE rj_import_group_settings"
        );
    }
}
