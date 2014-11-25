<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20141118093642 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE import_mapping (
                id BIGINT AUTO_INCREMENT NOT NULL,
                group_id BIGINT NOT NULL,
                header_hash VARCHAR(32) NOT NULL,
                mapping_data LONGTEXT NOT NULL COMMENT '(DC2Type:array)',
                INDEX IDX_5AF68566FE54D947 (group_id),
                UNIQUE INDEX unique_index_constraint (group_id, header_hash),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE import_mapping
                ADD CONSTRAINT FK_5AF68566FE54D947
                FOREIGN KEY (group_id)
                REFERENCES cj_account_group (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "DROP TABLE import_mapping"
        );
    }
}
