<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150427174007 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE partner
                ADD logo_name VARCHAR(255) DEFAULT NULL,
                ADD login_url VARCHAR(255) DEFAULT NULL,
                ADD address VARCHAR(255) DEFAULT NULL,
                ADD is_powered_by TINYINT(1) NOT NULL"
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
                DROP logo_name,
                DROP login_url,
                DROP address,
                DROP is_powered_by"
        );
    }
}
