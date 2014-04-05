<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140313130206 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
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
        $this->addSql(
            "ALTER TABLE `cj_operation` CHANGE `amount` `amount` DOUBLE( 10, 2 ) DEFAULT NULL"
        );
        $this->addSql(
            "UPDATE `cj_order` SET amount = 0 WHERE amount IS NULL"
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
        $this->addSql(
            "ALTER TABLE `cj_operation` CHANGE `amount` `amount` INT(11) DEFAULT NULL"
        );
    }
}
