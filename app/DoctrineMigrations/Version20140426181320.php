<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140426181320 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_order
                DROP days_late"
        );
        $this->addSql(
            "ALTER TABLE cj_operation
                CHANGE paid_for paid_for DATE NOT NULL,
                CHANGE type type ENUM('report','rent','other','charge')
                COMMENT '(DC2Type:OperationType)' DEFAULT 'report' NOT NULL"
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
                CHANGE paid_for paid_for DATE DEFAULT NULL,
                CHANGE type type ENUM('report','rent','charge')
                COMMENT '(DC2Type:OperationType)' DEFAULT 'report' NOT NULL"
        );
    }
}
