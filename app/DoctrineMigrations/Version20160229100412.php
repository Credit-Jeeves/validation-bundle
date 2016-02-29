<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160229100412 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE cj_applicant_pidkiq
                DROP try_num,
                CHANGE questions questions LONGTEXT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_user
                ADD verify_attempts INT DEFAULT 0 NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE cj_applicant_pidkiq
                ADD try_num BIGINT DEFAULT '0' NOT NULL,
                CHANGE questions questions LONGTEXT DEFAULT NULL"
        );

        $this->addSql(
            "ALTER TABLE cj_user
                DROP verify_attempts"
        );
    }
}
