<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20131205112215 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_deposit_account
                CHANGE merchant_name merchant_name VARCHAR(255) DEFAULT NULL"
        );

        $this->addSql(
            "ALTER TABLE rj_deposit_account
                ADD status ENUM('error','success','init','complete')
                    COMMENT '(DC2Type:DepositAccountStatus)' DEFAULT 'init' NOT NULL,
                ADD message VARCHAR(255) DEFAULT NULL"
        );

        // TODO: set status=complete for existing deposit accounts with merchant names
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_deposit_account
                DROP status,
                DROP message"
        );

        $this->addSql(
            "ALTER TABLE rj_deposit_account
                CHANGE merchant_name merchant_name VARCHAR(255) NOT NULL"
        );
    }
}
