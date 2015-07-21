<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150709123338 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE cj_order
                CHANGE type type ENUM('heartland_card','heartland_bank','bank','card','cash')
                    COMMENT '(DC2Type:OrderPaymentType)' DEFAULT NULL"
        );

        $this->addSql("
            UPDATE cj_order
            SET type='bank'
            WHERE type='heartland_bank';

            UPDATE cj_order
            SET type='card'
            WHERE type='heartland_card';"
        );

        $this->addSql(
            "ALTER TABLE cj_order
                CHANGE type payment_type ENUM('bank','card','cash')
                    COMMENT '(DC2Type:OrderPaymentType)' DEFAULT NULL"
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
                CHANGE payment_type type ENUM('bank','card','cash')
                    COMMENT '(DC2Type:OrderPaymentType)' DEFAULT NULL"
        );
    }
}
