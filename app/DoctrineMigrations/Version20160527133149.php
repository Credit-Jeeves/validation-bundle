<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160527133149 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_import
                CHANGE import_type import_type enum('property', 'lease')
                 COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:ImportModelType)'"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_import
                CHANGE import_type import_type enum('property', 'contract')
                 COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:ImportModelType)'"
        );
    }
}
