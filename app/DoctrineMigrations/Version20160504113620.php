<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160504113620 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_import
                CHANGE status status enum('running','complete','error')
                COMMENT '(DC2Type:ImportStatus)' DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_import
                ADD error_message LONGTEXT DEFAULT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_import
                CHANGE status status enum('running','complete')
                COMMENT '(DC2Type:ImportStatus)' DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_import
                DROP error_message"
        );
    }
}
