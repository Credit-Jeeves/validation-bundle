<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150618192642 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_transaction_outbound
                CHANGE transaction_id transaction_id INT DEFAULT NULL,
                ADD status ENUM('success','cancelled','error')
                    COMMENT '(DC2Type:OutboundTransactionStatus)' NOT NULL,
                ADD message VARCHAR(255) DEFAULT NULL"
        );

        $this->addSql(
            "ALTER TABLE cj_order
                CHANGE status status
                    ENUM('cancelled', 'complete', 'error', 'new', 'pending', 'refunded', 'returned', 'sending')
                        COMMENT '(DC2Type:OrderStatus)'
                    NOT NULL DEFAULT 'new'"
        );

    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_transaction_outbound
                DROP status,
                DROP message,
                CHANGE transaction_id transaction_id INT NOT NULL"
        );

        $this->addSql(
            "ALTER TABLE cj_order
                CHANGE status status
                    ENUM('cancelled', 'complete', 'error', 'new', 'pending', 'refunded', 'returned')
                        COMMENT '(DC2Type:OrderStatus)'
                    NOT NULL DEFAULT 'new'"
        );
    }
}
