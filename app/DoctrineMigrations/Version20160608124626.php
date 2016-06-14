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
                ADD unit_lookup_id VARCHAR(255) NOT NULL,
                ADD resident_update_mask VARCHAR(18) DEFAULT NULL,
                ADD resident_status_map VARCHAR(18) DEFAULT NULL,
                ADD lease_update_mask VARCHAR(18) DEFAULT NULL,
                ADD lease_diff_map VARCHAR(18) DEFAULT NULL,
                ADD resident_diff_map VARCHAR(18) DEFAULT NULL,
                ADD lease_status_map VARCHAR(18) DEFAULT NULL"
        );

        $this->addSql(
            "ALTER TABLE rj_import_lease
                DROP lease_status,
                DROp user_status"
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
                DROP unit_lookup_id,
                DROP resident_update_mask,
                DROP resident_status_map,
                DROP lease_update_mask,
                DROP lease_diff_map,
                DROP resident_diff_map,
                DROP lease_status_map"
        );

        $this->addSql(
            "ALTER TABLE rj_import_lease
                ADD lease_status ENUM('new','match','error')
                    COMMENT '(DC2Type:ImportLeaseStatus)' DEFAULT NULL"
        );

        $this->addSql(
            "ALTER TABLE rj_import_lease
                ADD user_status ENUM('invited','not_invited','no_email', 'bad_email', 'error')
                    COMMENT '(DC2Type:ImportLeaseStatus)' DEFAULT NULL"
        );
    }
}
