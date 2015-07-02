<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150717144227 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql("ALTER TABLE cj_order CHANGE status status
            enum('cancelled','complete','error','new','pending','refunded','refunding','reissued','returned','sending')
            NOT NULL DEFAULT 'new' COMMENT '(DC2Type:OrderStatus)'"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql("ALTER TABLE cj_order CHANGE status status
            enum('cancelled','complete','error','new','pending','refunded', 'returned','sending')
            COLLATE utf8_unicode_ci NOT NULL DEFAULT 'new' COMMENT '(DC2Type:OrderStatus)'"
        );
    }
}
