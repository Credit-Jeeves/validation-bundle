<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140313130207 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "DROP TABLE cj_order_operation"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                DROP INDEX UNIQ_21F5D92D2576E0FD,
                ADD INDEX IDX_21F5D92D2576E0FD (contract_id)"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                ADD order_id BIGINT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                ADD CONSTRAINT FK_21F5D92D8D9F6D38
                FOREIGN KEY (order_id)
                REFERENCES cj_order (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_21F5D92D8D9F6D38 ON cj_operation (order_id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
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
            "ALTER TABLE cj_operation
                DROP INDEX IDX_21F5D92D2576E0FD,
                ADD UNIQUE INDEX UNIQ_21F5D92D2576E0FD (contract_id)"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                DROP
                FOREIGN KEY FK_21F5D92D8D9F6D38"
        );
        $this->addSql(
            "DROP INDEX IDX_21F5D92D8D9F6D38 ON cj_operation"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                DROP order_id"
        );
    }
}
