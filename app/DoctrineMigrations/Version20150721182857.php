<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150721182857 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_order
                CHANGE status status
                ENUM
                ('cancelled','complete','error','new','pending','refunded','refunding','reissued','returned','sending')
                    COMMENT '(DC2Type:OrderStatus)' NOT NULL,
                CHANGE payment_type payment_type ENUM('bank','card','cash')
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
                CHANGE status status
                ENUM
                ('cancelled','complete','error','new','pending','refunded','refunding','reissued','returned','sending')
                    COMMENT '(DC2Type:OrderStatus)' DEFAULT 'new',
                CHANGE payment_type payment_type ENUM('bank','card','cash')
                    COMMENT '(DC2Type:OrderPaymentType)' DEFAULT NULL"
        );
    }
}
