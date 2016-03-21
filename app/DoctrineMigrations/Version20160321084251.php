<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160321084251 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE cj_user
                CHANGE email email VARCHAR(255) DEFAULT NULL,
                CHANGE email_canonical email_canonical VARCHAR(255) DEFAULT NULL"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE cj_user
                CHANGE email email VARCHAR(255) NOT NULL,
                CHANGE email_canonical email_canonical VARCHAR(255) NOT NULL"
        );
    }
}
