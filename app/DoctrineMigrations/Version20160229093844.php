<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160229093844 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_order
                ADD check_number VARCHAR(255) DEFAULT NULL,
                CHANGE payment_type payment_type enum('bank','card','cash','scanned_check')
                COMMENT '(DC2Type:OrderPaymentType)' NOT NULL"
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
                DROP check_number,
                CHANGE payment_type payment_type enum('bank','card','cash')
                COMMENT '(DC2Type:OrderPaymentType)' NOT NULL"
        );
    }
}
