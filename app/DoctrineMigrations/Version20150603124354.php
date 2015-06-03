<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150603124354 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_payment_account
                ADD bank_account_type ENUM('checking','savings','business checking')
                    COMMENT '(DC2Type:BankAccountType)' DEFAULT NULL"
        );

        $this->addSql(
            "ALTER TABLE rj_billing_account
                ADD bank_account_type ENUM('checking','savings','business checking')
                    COMMENT '(DC2Type:BankAccountType)' DEFAULT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_payment_account
                DROP bank_account_type"
        );

        $this->addSql(
            "ALTER TABLE rj_billing_account
                DROP bank_account_type"
        );
    }
}
