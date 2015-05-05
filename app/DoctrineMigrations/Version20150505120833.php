<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150505120833 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "CREATE TABLE rj_import_summary (id BIGINT AUTO_INCREMENT NOT NULL,
                group_id BIGINT DEFAULT NULL,
                public_id INT DEFAULT NULL,
                type ENUM('single_property','multi_properties','multi_groups')
                    COMMENT '(DC2Type:ImportType)' NOT NULL,
                count_total INT NOT NULL,
                count_new INT NOT NULL,
                count_match INT NOT NULL,
                count_invite INT NOT NULL,
                count_skipped INT NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX IDX_62070945FE54D947 (group_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE rj_import_error (id BIGINT AUTO_INCREMENT NOT NULL,
                import_summary_id BIGINT DEFAULT NULL,
                exception_uid INT DEFAULT NULL,
                row_offset INT NOT NULL,
                row_content LONGTEXT NOT NULL
                    COMMENT '(DC2Type:json_array)',
                messages LONGTEXT NOT NULL
                    COMMENT '(DC2Type:json_array)',
                INDEX IDX_C2440AFCE848EA4F (import_summary_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_import_summary
                ADD CONSTRAINT FK_62070945FE54D947
                FOREIGN KEY (group_id)
                REFERENCES cj_account_group (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_import_error
                ADD CONSTRAINT FK_C2440AFCE848EA4F
                FOREIGN KEY (import_summary_id)
                REFERENCES rj_import_summary (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_import_error
                DROP
                FOREIGN KEY FK_C2440AFCE848EA4F"
        );
        $this->addSql(
            "DROP TABLE rj_import_summary"
        );
        $this->addSql(
            "DROP TABLE rj_import_error"
        );
    }
}
