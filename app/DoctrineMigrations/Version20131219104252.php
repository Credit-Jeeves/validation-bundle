<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20131219104252 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE cj_order
                CHANGE amount amount NUMERIC(10,
                2) DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_payment
                CHANGE amount amount NUMERIC(10,
                2) NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE cj_order
                CHANGE amount amount INT DEFAULT NULL"
        );

        $this->addSql(
            "ALTER TABLE rj_payment
                CHANGE amount amount NUMERIC(10,
                0) NOT NULL"
        );
    }
}
