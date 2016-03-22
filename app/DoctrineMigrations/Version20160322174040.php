<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160322174040 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE jms_cron_jobs (id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                command VARCHAR(200) NOT NULL,
                lastRunAt DATETIME NOT NULL,
                UNIQUE INDEX UNIQ_55F5ED428ECAEAD4 (command),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );

        $this->addSql(
            "ALTER TABLE jms_jobs
                ADD queue VARCHAR(50) NOT NULL,
                ADD priority SMALLINT NOT NULL,
                ADD workerName VARCHAR(50) DEFAULT NULL,
                CHANGE state state VARCHAR(15) NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "DROP TABLE jms_cron_jobs"
        );

        $this->addSql(
            "ALTER TABLE jms_jobs
                DROP queue,
                DROP priority,
                DROP workerName,
                CHANGE state state VARCHAR(255) NOT NULL"
        );
    }
}
