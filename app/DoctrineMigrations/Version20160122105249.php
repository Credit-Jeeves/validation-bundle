<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160122105249 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "CREATE TABLE rj_profitstars_settings (id BIGINT AUTO_INCREMENT NOT NULL,
                holding_id BIGINT NOT NULL,
                merchant_id VARCHAR(255) NOT NULL,
                INDEX IDX_187622E06CD5FBA3 (holding_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_profitstars_settings
                ADD CONSTRAINT FK_187622E06CD5FBA3
                FOREIGN KEY (holding_id)
                REFERENCES cj_holding (id)"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "DROP TABLE rj_profitstars_settings"
        );
    }
}
