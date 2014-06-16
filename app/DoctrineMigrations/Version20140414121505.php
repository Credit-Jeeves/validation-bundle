<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140414121505 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_resident_mapping (id BIGINT AUTO_INCREMENT NOT NULL,
                tenant_id BIGINT NOT NULL,
                holding_id BIGINT NOT NULL,
                resident_id VARCHAR(128) NOT NULL,
                first_name VARCHAR(255) NOT NULL,
                last_name VARCHAR(255) NOT NULL,
                INDEX IDX_A9845E989033212A (tenant_id),
                INDEX IDX_A9845E986CD5FBA3 (holding_id),
                UNIQUE INDEX unique_index_constraint (tenant_id,
                holding_id,
                resident_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_resident_mapping
                ADD CONSTRAINT FK_A9845E989033212A
                FOREIGN KEY (tenant_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_resident_mapping
                ADD CONSTRAINT FK_A9845E986CD5FBA3
                FOREIGN KEY (holding_id)
                REFERENCES cj_holding (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_user
                DROP resident_id"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "DROP TABLE rj_resident_mapping"
        );
        $this->addSql(
            "ALTER TABLE cj_user
                ADD resident_id VARCHAR(128) DEFAULT NULL"
        );
    }
}
