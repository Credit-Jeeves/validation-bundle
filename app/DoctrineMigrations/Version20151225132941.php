<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151225132941 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "CREATE TABLE rj_import_transformer (id BIGINT AUTO_INCREMENT NOT NULL,
                holding_id BIGINT DEFAULT NULL,
                group_id BIGINT DEFAULT NULL,
                external_property_id VARCHAR(255) DEFAULT NULL,
                class_name VARCHAR(255) NOT NULL,
                INDEX IDX_6DE030FC6CD5FBA3 (holding_id),
                INDEX IDX_6DE030FCFE54D947 (group_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_import_transformer
                ADD CONSTRAINT FK_6DE030FC6CD5FBA3
                FOREIGN KEY (holding_id)
                REFERENCES cj_holding (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_import_transformer
                ADD CONSTRAINT FK_6DE030FCFE54D947
                FOREIGN KEY (group_id)
                REFERENCES rj_group (id)"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "DROP TABLE rj_import_transformer"
        );
    }
}
