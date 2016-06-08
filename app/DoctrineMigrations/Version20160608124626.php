<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160608124626 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_import_lease
                ADD unit_look_up_id VARCHAR(255) NOT NULL,
                ADD resident_update_mask VARCHAR(255) DEFAULT NULL,
                ADD resident_status_map ENUM('invited','not_invited','no_email','bad_email','error')
                    COMMENT '(DC2Type:ResidentStatusMapType)' DEFAULT NULL,
                ADD lease_update_mask VARCHAR(255) DEFAULT NULL,
                ADD lease_diff_map VARCHAR(255) DEFAULT NULL,
                ADD resident_diff_map VARCHAR(255) DEFAULT NULL,
                ADD lease_status_map ENUM('new','match','no_email','error')
                    COMMENT '(DC2Type:LeaseStatusMapType)' DEFAULT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_import_lease
                DROP unit_look_up_id,
                DROP resident_update_mask,
                DROP resident_status_map,
                DROP lease_update_mask,
                DROP lease_diff_map,
                DROP resident_diff_map,
                DROP lease_status_map"
        );
    }
}
