<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160329103939 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "CREATE TABLE rj_profitstars_batch (id INT AUTO_INCREMENT NOT NULL,
                holding_id BIGINT NOT NULL,
                batch_number VARCHAR(255) NOT NULL,
                status ENUM('open','closed')
                    COMMENT '(DC2Type:ProfitStarsBatchStatus)' NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE INDEX UNIQ_AD9E08F3456B7924 (batch_number),
                INDEX IDX_AD9E08F36CD5FBA3 (holding_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_profitstars_batch
                ADD CONSTRAINT FK_AD9E08F36CD5FBA3
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
            "DROP TABLE rj_profitstars_batch"
        );
    }
}
