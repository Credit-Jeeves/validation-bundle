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
            "INSERT INTO rj_property_address
            (state, city, street, number, zip, google_reference, jb, kb, is_single, ss_lat, ss_long, created_at, updated_at, ss_index)
              SELECT
                IFNULL(area,''), IFNULL(city,''), IFNULL(street,''),
                IFNULL(number,''), IFNULL(zip,'') as zip1,
                google_reference, jb, kb, is_single, jb, kb, NOW(), NOW(),
                REPLACE(CONCAT(IFNULL(number,''), IFNULL(street,''), IFNULL(city,''), IFNULL(area,''), 'InvalidAddress'),' ','')
              FROM rj_property
              WHERE ss_index IS NULL AND rj_property.property_address_id IS NULL
            "
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
        $this->addSql(
            "UPDATE rj_property p
            SET property_address_id = (
                SELECT pa.id
                FROM rj_property_address pa
                WHERE pa.ss_index =
                  REPLACE(CONCAT(IFNULL(number,''), IFNULL(street,''), IFNULL(city,''), IFNULL(area,''), 'InvalidAddress'),' ','')
                LIMIT 1
            )
            WHERE ss_index IS NULL AND p.property_address_id IS NULL"
        );

        $this->addSql(
            "ALTER TABLE rj_property
                CHANGE property_address_id property_address_id BIGINT NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
