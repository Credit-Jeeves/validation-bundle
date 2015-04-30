<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150122100833 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                CHANGE yardi_payment_accepted payment_accepted  ENUM('0','1','2')
                    DEFAULT '0' NOT NULL"
        );

        $this->addSql(
            "ALTER TABLE rj_contract
                CHANGE yardi_payment_accepted payment_accepted ENUM('0','1','2')
                    DEFAULT '0' NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                CHANGE payment_accepted yardi_payment_accepted ENUM('0','1','2')
                    DEFAULT '0' NOT NULL"
        );

        $this->addSql(
            "ALTER TABLE rj_contract
                CHANGE payment_accepted yardi_payment_accepted ENUM('0','1','2')
                     DEFAULT '0' NOT NULL"
        );
    }
}
