<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20140116164550 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "CREATE TABLE partner (id BIGINT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                request_name VARCHAR(255) NOT NULL,
                UNIQUE INDEX UNIQ_312B3E165E237E06 (name),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE partner_code (id INT AUTO_INCREMENT NOT NULL,
                partner_id BIGINT DEFAULT NULL,
                user_id BIGINT DEFAULT NULL,
                code VARCHAR(255) DEFAULT NULL,
                payment_date DATE DEFAULT NULL,
                is_charged TINYINT(1) DEFAULT '0' NOT NULL,
                INDEX IDX_272103809393F8FE (partner_id),
                UNIQUE INDEX UNIQ_27210380A76ED395 (user_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE partner_code
                ADD CONSTRAINT FK_272103809393F8FE
                FOREIGN KEY (partner_id)
                REFERENCES partner (id)"
        );
        $this->addSql(
            "ALTER TABLE partner_code
                ADD CONSTRAINT FK_27210380A76ED395
                FOREIGN KEY (user_id)
                REFERENCES cj_user (id)"
        );

        $this->addSql(
            "INSERT INTO partner SET name = 'creditcom', request_name = 'CREDITCOM'"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE partner_code
                DROP
                FOREIGN KEY FK_272103809393F8FE"
        );
        $this->addSql(
            "DROP TABLE partner"
        );
        $this->addSql(
            "DROP TABLE partner_code"
        );
    }
}
