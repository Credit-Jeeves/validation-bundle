<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151118191114 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "INSERT INTO rj_property_address
            (state, city, street, number, zip, google_reference, jb, kb, is_single, ss_lat, ss_long, ss_index, created_at, updated_at)
            SELECT
            area, city, street, number, zip, google_reference, jb, kb, is_single, ss_lat, ss_long, ss_index, NOW(), NOW()
            FROM rj_property
            WHERE ss_index IS NOT NULL AND rj_property.property_address_id IS NULL
            GROUP BY ss_index"
        );
        $this->addSql(
            "UPDATE rj_property p
            SET property_address_id = (
                SELECT pa.id
                FROM rj_property_address pa
                WHERE p.ss_index = pa.ss_index
            )
            WHERE ss_index IS NOT NULL AND p.property_address_id IS NULL"
        );
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
