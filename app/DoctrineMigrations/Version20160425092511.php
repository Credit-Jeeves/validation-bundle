<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160425092511 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "DROP TABLE IF EXISTS rj_profitstars_cmid"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "CREATE TABLE rj_profitstars_cmid (id BIGINT AUTO_INCREMENT NOT NULL,
                landlord_id BIGINT NOT NULL,
                cmid VARCHAR(255) NOT NULL,
                UNIQUE INDEX UNIQ_12A87977D48E7AED (landlord_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_profitstars_cmid
                ADD CONSTRAINT FK_12A87977D48E7AED
                FOREIGN KEY (landlord_id)
                REFERENCES cj_user (id)"
        );
    }
}
