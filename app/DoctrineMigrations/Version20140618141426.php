<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140618141426 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_unit_mapping (id BIGINT AUTO_INCREMENT NOT NULL,
                unit_id BIGINT NOT NULL,
                external_unit_id VARCHAR(128) NOT NULL,
                UNIQUE INDEX UNIQ_6F633B0BF8BD700D (unit_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_unit_mapping
                ADD CONSTRAINT FK_6F633B0BF8BD700D
                FOREIGN KEY (unit_id)
                REFERENCES rj_unit (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "DROP TABLE rj_unit_mapping"
        );
    }
}
