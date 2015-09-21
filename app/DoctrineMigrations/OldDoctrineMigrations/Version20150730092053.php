<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150730092053 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "CREATE TABLE rj_import_mapping_by_property (id BIGINT AUTO_INCREMENT NOT NULL,
                property_id BIGINT NOT NULL,
                mapping_data LONGTEXT NOT NULL
                    COMMENT '(DC2Type:array)',
                UNIQUE INDEX UNIQ_4F12A8AA549213EC (property_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_import_mapping_by_property
                ADD CONSTRAINT FK_4F12A8AA549213EC
                FOREIGN KEY (property_id)
                REFERENCES rj_property (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "DROP TABLE rj_import_mapping_by_property"
        );
    }
}
