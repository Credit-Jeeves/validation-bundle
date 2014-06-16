<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140313130208 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_operation
                CHANGE order_id order_id BIGINT NOT NULL"
        );
        $this->addSql(
            "DROP TABLE cj_order_operation"
        );
        $this->addSql(
            "ALTER TABLE `cj_operation` CHANGE `amount` `amount` DOUBLE( 10, 2 ) NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_operation
                CHANGE order_id order_id BIGINT DEFAULT NULL"
        );
        $this->addSql(
            "CREATE TABLE cj_order_operation (cj_order_id BIGINT NOT NULL,
                cj_operation_id BIGINT NOT NULL,
                INDEX IDX_1FF923042122E99A (cj_order_id),
                INDEX IDX_1FF92304CBF96867 (cj_operation_id),
                PRIMARY KEY(cj_order_id,
                cj_operation_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE cj_order_operation
                ADD CONSTRAINT FK_1FF92304CBF96867
                FOREIGN KEY (cj_operation_id)
                REFERENCES cj_operation (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_order_operation
                ADD CONSTRAINT FK_1FF923042122E99A
                FOREIGN KEY (cj_order_id)
                REFERENCES cj_order (id)"
        );
        $this->addSql(
            "ALTER TABLE `cj_operation` CHANGE `amount` `amount` DOUBLE( 10, 2 ) DEFAULT NULL"
        );
    }
}
