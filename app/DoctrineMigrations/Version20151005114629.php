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
            "CREATE TABLE rj_binlist_bank (id BIGINT AUTO_INCREMENT NOT NULL,
                bank_name VARCHAR(255) NOT NULL,
                low_debit_fee TINYINT(1) DEFAULT '0' NOT NULL,
                UNIQUE INDEX UNIQ_5260C5F8F4432C88 (bank_name),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE rj_debit_card_binlist (id BIGINT AUTO_INCREMENT NOT NULL,
                iin INT NOT NULL,
                card_brand VARCHAR(255) DEFAULT NULL,
                card_sub_brand VARCHAR(255) DEFAULT NULL,
                card_type VARCHAR(255) DEFAULT NULL,
                card_category VARCHAR(255) DEFAULT NULL,
                country_code VARCHAR(255) DEFAULT NULL,
                bank_id BIGINT NOT NULL,
                bank_url VARCHAR(255) DEFAULT NULL,
                bank_phone VARCHAR(255) DEFAULT NULL,
                bank_city VARCHAR(255) DEFAULT NULL,
                UNIQUE INDEX UNIQ_158A514BA672B50C (iin),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_debit_card_binlist
                ADD CONSTRAINT FK_158A514B11C8FB41
                FOREIGN KEY (bank_id)
                REFERENCES rj_binlist_bank (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_158A514B11C8FB41 ON rj_debit_card_binlist (bank_id)"
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
        $this->addSql(
            "DROP TABLE rj_binlist_bank"
        );
    }
}
