<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140108102042 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE jms_jobs (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
                state VARCHAR(255) NOT NULL,
                createdAt DATETIME NOT NULL,
                startedAt DATETIME DEFAULT NULL,
                checkedAt DATETIME DEFAULT NULL,
                executeAfter DATETIME DEFAULT NULL,
                closedAt DATETIME DEFAULT NULL,
                command VARCHAR(255) NOT NULL,
                args LONGTEXT NOT NULL
                    COMMENT '(DC2Type:json_array)',
                output LONGTEXT DEFAULT NULL,
                errorOutput LONGTEXT DEFAULT NULL,
                exitCode SMALLINT UNSIGNED DEFAULT NULL,
                maxRuntime SMALLINT UNSIGNED NOT NULL,
                maxRetries SMALLINT UNSIGNED NOT NULL,
                stackTrace LONGBLOB DEFAULT NULL
                    COMMENT '(DC2Type:jms_job_safe_object)',
                runtime SMALLINT UNSIGNED DEFAULT NULL,
                memoryUsage INT UNSIGNED DEFAULT NULL,
                memoryUsageReal INT UNSIGNED DEFAULT NULL,
                originalJob_id BIGINT UNSIGNED DEFAULT NULL,
                INDEX IDX_704ADB9349C447F1 (originalJob_id),
                INDEX IDX_704ADB938ECAEAD4 (command),
                INDEX job_runner (executeAfter,
                state),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE jms_job_dependencies (source_job_id BIGINT UNSIGNED NOT NULL,
                dest_job_id BIGINT UNSIGNED NOT NULL,
                INDEX IDX_8DCFE92CBD1F6B4F (source_job_id),
                INDEX IDX_8DCFE92C32CF8D4C (dest_job_id),
                PRIMARY KEY(source_job_id,
                dest_job_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE jms_job_related_entities (job_id BIGINT UNSIGNED NOT NULL,
                related_class VARCHAR(150) NOT NULL,
                related_id VARCHAR(100) NOT NULL,
                INDEX IDX_E956F4E2BE04EA9 (job_id),
                PRIMARY KEY(job_id,
                related_class,
                related_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE jms_job_statistics (job_id BIGINT UNSIGNED NOT NULL,
                characteristic VARCHAR(30) NOT NULL,
                createdAt DATETIME NOT NULL,
                charValue DOUBLE PRECISION NOT NULL,
                PRIMARY KEY(job_id,
                characteristic,
                createdAt)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE jms_jobs
                ADD CONSTRAINT FK_704ADB9349C447F1
                FOREIGN KEY (originalJob_id)
                REFERENCES jms_jobs (id)"
        );
        $this->addSql(
            "ALTER TABLE jms_job_dependencies
                ADD CONSTRAINT FK_8DCFE92CBD1F6B4F
                FOREIGN KEY (source_job_id)
                REFERENCES jms_jobs (id)"
        );
        $this->addSql(
            "ALTER TABLE jms_job_dependencies
                ADD CONSTRAINT FK_8DCFE92C32CF8D4C
                FOREIGN KEY (dest_job_id)
                REFERENCES jms_jobs (id)"
        );
        $this->addSql(
            "ALTER TABLE jms_job_related_entities
                ADD CONSTRAINT FK_E956F4E2BE04EA9
                FOREIGN KEY (job_id)
                REFERENCES jms_jobs (id)"
        );
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE jms_jobs
                DROP
                FOREIGN KEY FK_704ADB9349C447F1"
        );
        $this->addSql(
            "ALTER TABLE jms_job_dependencies
                DROP
                FOREIGN KEY FK_8DCFE92CBD1F6B4F"
        );
        $this->addSql(
            "ALTER TABLE jms_job_dependencies
                DROP
                FOREIGN KEY FK_8DCFE92C32CF8D4C"
        );
        $this->addSql(
            "ALTER TABLE jms_job_related_entities
                DROP
                FOREIGN KEY FK_E956F4E2BE04EA9"
        );
        $this->addSql(
            "DROP TABLE jms_jobs"
        );
        $this->addSql(
            "DROP TABLE jms_job_dependencies"
        );
        $this->addSql(
            "DROP TABLE jms_job_related_entities"
        );
        $this->addSql(
            "DROP TABLE jms_job_statistics"
        );
    }
}
