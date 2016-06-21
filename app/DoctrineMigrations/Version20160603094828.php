<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160603094828 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_import_transformer
                ADD import_type ENUM('property','lease')
                    COMMENT '(DC2Type:ImportModelType)' DEFAULT NULL"
        );

        $this->addSql("UPDATE rj_import_transformer SET import_type = 'property'");

        $this->addSql(
            "ALTER TABLE rj_import_transformer
                CHANGE import_type import_type ENUM('property','lease')
                COMMENT '(DC2Type:ImportModelType)' NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_import_transformer
                DROP import_type"
        );
    }
}
