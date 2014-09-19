<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140921184831 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE order_external_api (id BIGINT AUTO_INCREMENT NOT NULL,
                order_id BIGINT DEFAULT NULL,
                apiType ENUM('yardi')
                    COMMENT '(DC2Type:ExternalApi)' DEFAULT 'yardi' NOT NULL,
                created_at DATETIME NOT NULL,
                UNIQUE INDEX UNIQ_9EFE2AD8D9F6D38 (order_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE order_external_api
                ADD CONSTRAINT FK_9EFE2AD8D9F6D38
                FOREIGN KEY (order_id)
                REFERENCES cj_order (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_order
                ADD external_api_id BIGINT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_order
                ADD CONSTRAINT FK_DA53B53D2015A6D1
                FOREIGN KEY (external_api_id)
                REFERENCES order_external_api (id)"
        );
        $this->addSql(
            "CREATE UNIQUE INDEX UNIQ_DA53B53D2015A6D1 ON cj_order (external_api_id)"
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
                DROP
                FOREIGN KEY FK_DA53B53D2015A6D1"
        );
        $this->addSql(
            "DROP TABLE order_external_api"
        );
        $this->addSql(
            "DROP INDEX UNIQ_DA53B53D2015A6D1 ON cj_order"
        );
        $this->addSql(
            "ALTER TABLE cj_order
                DROP external_api_id"
        );
    }
}
