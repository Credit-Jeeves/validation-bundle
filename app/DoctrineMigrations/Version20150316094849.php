<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150316094849 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_mri_settings
                ADD site_id LONGTEXT NOT NULL,
                ADD payment_type LONGTEXT NOT NULL,
                ADD source_code LONGTEXT NOT NULL,
                ADD cash_type LONGTEXT NOT NULL,
                ADD charge_code LONGTEXT NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_mri_settings
                DROP site_id,
                DROP payment_type,
                DROP source_code,
                DROP cash_type,
                DROP charge_code"
        );
    }
}
