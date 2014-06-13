<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140508123110 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_property_mapping (id BIGINT AUTO_INCREMENT NOT NULL,
                property_id BIGINT NOT NULL,
                holding_id BIGINT NOT NULL,
                landlord_property_id VARCHAR(128) NOT NULL,
                INDEX IDX_5339818C549213EC (property_id),
                INDEX IDX_5339818C6CD5FBA3 (holding_id),
                UNIQUE INDEX unique_index_constraint (property_id,
                holding_id,
                landlord_property_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_property_mapping
                ADD CONSTRAINT FK_5339818C549213EC
                FOREIGN KEY (property_id)
                REFERENCES rj_property (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_property_mapping
                ADD CONSTRAINT FK_5339818C6CD5FBA3
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
            "DROP TABLE rj_property_mapping"
        );
    }
}
