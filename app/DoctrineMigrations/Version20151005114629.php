<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151005114629 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Migrations\AbortMigrationExceptions
     */
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "CREATE TABLE rj_debit_card_binlist (id BIGINT AUTO_INCREMENT NOT NULL,
                iin INT NOT NULL,
                card_brand VARCHAR(255) DEFAULT NULL,
                card_sub_brand VARCHAR(255) DEFAULT NULL,
                card_type VARCHAR(255) DEFAULT NULL,
                card_category VARCHAR(255) DEFAULT NULL,
                country_code VARCHAR(255) DEFAULT NULL,
                bank_name VARCHAR(255) DEFAULT NULL,
                bank_url VARCHAR(255) DEFAULT NULL,
                bank_phone VARCHAR(255) DEFAULT NULL,
                bank_city VARCHAR(255) DEFAULT NULL,
                UNIQUE INDEX UNIQ_158A514BA672B50C (iin),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Migrations\AbortMigrationException
     */
    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP TABLE rj_debit_card_binlist"
        );
    }
}
