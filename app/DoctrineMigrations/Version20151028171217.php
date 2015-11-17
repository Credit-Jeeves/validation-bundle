<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151028171217 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE cj_order
                ADD payment_id BIGINT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_order
                ADD CONSTRAINT FK_DA53B53D4C3A3BB
                FOREIGN KEY (payment_id)
                REFERENCES rj_payment (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_DA53B53D4C3A3BB ON cj_order (payment_id)"
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
                FOREIGN KEY FK_DA53B53D4C3A3BB"
        );
        $this->addSql(
            "DROP INDEX IDX_DA53B53D4C3A3BB ON cj_order"
        );
        $this->addSql(
            "ALTER TABLE cj_order
                DROP payment_id"
        );
    }
}
