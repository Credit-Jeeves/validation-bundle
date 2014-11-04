<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20141104091815 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP INDEX unique_index_constraint ON rj_property_mapping"
        );
        $this->addSql(
            "ALTER TABLE rj_property_mapping
                CHANGE landlord_property_id external_property_id VARCHAR(128) NOT NULL"
        );
        $this->addSql(
            "CREATE UNIQUE INDEX unique_index_constraint ON rj_property_mapping (property_id,
                holding_id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "DROP INDEX unique_index_constraint ON rj_property_mapping"
        );
        $this->addSql(
            "ALTER TABLE rj_property_mapping
                CHANGE external_property_id landlord_property_id VARCHAR(128) NOT NULL"
        );
        $this->addSql(
            "CREATE UNIQUE INDEX unique_index_constraint ON rj_property_mapping (property_id,
                holding_id,
                landlord_property_id)"
        );
    }
}
