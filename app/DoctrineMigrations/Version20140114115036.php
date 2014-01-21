<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20140114115036 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_order
                CHANGE status status ENUM('new','complete','error','cancelled','refunded','returned')
                    COMMENT '(DC2Type:OrderStatus)' DEFAULT 'new' NOT NULL"
        );
    }

    public function down(Schema $schema)
    {

    }
} 
