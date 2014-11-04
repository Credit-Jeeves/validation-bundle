<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20141031164328 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE partner
                ADD client_id INT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE partner
                ADD CONSTRAINT FK_312B3E1619EB6921
                FOREIGN KEY (client_id)
                REFERENCES client (id)"
        );
        $this->addSql(
            "CREATE UNIQUE INDEX UNIQ_312B3E1619EB6921 ON partner (client_id)"
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
                DROP client_id"
        );
    }
}