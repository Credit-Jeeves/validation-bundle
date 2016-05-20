<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151213112239 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "DROP TABLE rj_import_mapping_by_property"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "CREATE TABLE rj_import_mapping_by_property (id BIGINT AUTO_INCREMENT NOT NULL,
                property_id BIGINT NOT NULL,
                mapping_data LONGTEXT NOT NULL
                    COMMENT '(DC2Type:array)',
                UNIQUE INDEX UNIQ_B2B8A811549213EC (property_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
    }
}
