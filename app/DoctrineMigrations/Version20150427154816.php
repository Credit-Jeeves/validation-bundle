<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150427154816 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE partner
                ADD logoName VARCHAR(255) NOT NULL,
                ADD loginUrl VARCHAR(255) NOT NULL,
                ADD address VARCHAR(255) NOT NULL,
                ADD isPoweredBy TINYINT(1) NOT NULL"
        );

    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE partner
                DROP logoName,
                DROP loginUrl,
                DROP address,
                DROP isPoweredBy"
        );
    }
}
