<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140426181137 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_payment
                ADD total NUMERIC(10, 2) NOT NULL,
                ADD paid_for DATE DEFAULT NULL,
                CHANGE amount amount NUMERIC(10, 2) DEFAULT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE rj_payment
                DROP total
                DROP paid_for,
                CHANGE amount amount NUMERIC(10, 2) NOT NULL"
        );
    }
}
