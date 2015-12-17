<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151215203313 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "CREATE TABLE rj_import_property (
                id BIGINT AUTO_INCREMENT NOT NULL,
                import_id BIGINT NOT NULL,
                external_property_id VARCHAR(255) DEFAULT NULL,
                external_building_id VARCHAR(255) DEFAULT NULL,
                address_has_units TINYINT(1) DEFAULT '0' NOT NULL,
                property_has_buildings TINYINT(1) DEFAULT '0' NOT NULL,
                unit_name VARCHAR(255) DEFAULT NULL,
                external_unit_id VARCHAR(255) DEFAULT NULL,
                street_number VARCHAR(255) DEFAULT NULL,
                street_name VARCHAR(255) DEFAULT NULL,
                city VARCHAR(255) DEFAULT NULL,
                state VARCHAR(255) DEFAULT NULL,
                zip VARCHAR(15) DEFAULT NULL,
                status ENUM('none','error','new_unit','new_property_and_unit','match')
                    COMMENT '(DC2Type:ImportPropertyStatus)' DEFAULT NULL,
                error_messages LONGTEXT DEFAULT NULL
                    COMMENT '(DC2Type:array)',
                is_processed TINYINT(1) DEFAULT '0' NOT NULL,
                INDEX IDX_5953B64CB6A263D9 (import_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE rj_import (
                id BIGINT AUTO_INCREMENT NOT NULL,
                group_id BIGINT NOT NULL,
                user_id BIGINT NOT NULL,
                importType ENUM('property','contract')
                    COMMENT '(DC2Type:ImportModelType)' NOT NULL,
                status ENUM('running','complete')
                    COMMENT '(DC2Type:ImportStatus)' NOT NULL,
                created_at DATETIME NOT NULL,
                finished_at DATETIME DEFAULT NULL,
                INDEX IDX_8066B56EFE54D947 (group_id),
                INDEX IDX_8066B56EA76ED395 (user_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_import_property
                ADD CONSTRAINT FK_5953B64CB6A263D9
                FOREIGN KEY (import_id)
                REFERENCES rj_import (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_import
                ADD CONSTRAINT FK_8066B56EFE54D947
                FOREIGN KEY (group_id)
                REFERENCES rj_group (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_import
                ADD CONSTRAINT FK_8066B56EA76ED395
                FOREIGN KEY (user_id)
                REFERENCES cj_user (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_import_property
                DROP
                FOREIGN KEY FK_5953B64CB6A263D9"
        );

        $this->addSql(
            "DROP TABLE rj_import_property"
        );
        $this->addSql(
            "DROP TABLE rj_import"
        );
    }
}
