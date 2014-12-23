<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20141223124742 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "CREATE TABLE `session` (
                `session_id` varchar(255) NOT NULL,
                `session_value` text NOT NULL,
                `session_time` int(11) NOT NULL,
                PRIMARY KEY (`session_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP TABLE `session`"
        );
    }
}
