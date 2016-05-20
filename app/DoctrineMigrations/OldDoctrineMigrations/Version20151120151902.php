<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151120151902 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_mri_settings
                ADD send_description TINYINT(1) NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_mri_settings
                DROP send_description"
        );
    }
}
