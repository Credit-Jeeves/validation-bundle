<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151030122509 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "CREATE TABLE rj_import_api_mapping (id BIGINT AUTO_INCREMENT NOT NULL,
                holding_id BIGINT DEFAULT NULL,
                external_property_id VARCHAR(255) NOT NULL,
                mapping_data LONGTEXT NOT NULL
                    COMMENT '(DC2Type:array)',
                INDEX IDX_AF76F3BC6CD5FBA3 (holding_id),
                UNIQUE INDEX unique_external_property_id (holding_id,
                external_property_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );

        $this->addSql(
            "ALTER TABLE rj_import_api_mapping
                ADD CONSTRAINT FK_AF76F3BC6CD5FBA3
                FOREIGN KEY (holding_id)
                REFERENCES cj_holding (id)"
        );

        $this->addSql("
            INSERT INTO rj_import_api_mapping (holding_id, external_property_id, mapping_data)
            SELECT
              rj_property_mapping.holding_id,
              rj_property_mapping.external_property_id,
              rj_import_mapping_by_property.mapping_data
            FROM rj_import_mapping_by_property
            INNER JOIN rj_property_mapping ON
            rj_import_mapping_by_property.property_id = rj_property_mapping.property_id"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP TABLE rj_import_api_mapping"
        );
    }
}
