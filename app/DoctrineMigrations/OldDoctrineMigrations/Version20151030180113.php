<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151030180113 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_property
                ADD property_address_id BIGINT DEFAULT NULL,
                CHANGE country country VARCHAR(3) DEFAULT NULL,
                CHANGE city city VARCHAR(255) DEFAULT NULL"
        );

        $this->addSql(
            "CREATE TABLE rj_property_address (id BIGINT AUTO_INCREMENT NOT NULL,
                state VARCHAR(255) NOT NULL,
                city VARCHAR(255) NOT NULL,
                street VARCHAR(255) NOT NULL,
                number VARCHAR(255) NOT NULL,
                zip VARCHAR(15) NOT NULL,
                google_reference VARCHAR(255) DEFAULT NULL,
                jb DOUBLE PRECISION DEFAULT NULL,
                kb DOUBLE PRECISION DEFAULT NULL,
                is_single TINYINT(1) DEFAULT NULL,
                ss_lat DOUBLE PRECISION DEFAULT NULL,
                ss_long DOUBLE PRECISION DEFAULT NULL,
                ss_index VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );

        $this->addSql(
            "ALTER TABLE rj_property
                ADD CONSTRAINT FK_4837837740168F46
                FOREIGN KEY (property_address_id)
                REFERENCES rj_property_address (id)"
        );

        $this->addSql(
            "CREATE INDEX IDX_4837837740168F46 ON rj_property (property_address_id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "DROP TABLE rj_property_address"
        );
        $this->addSql(
            "ALTER TABLE rj_property
                DROP property_address_id,
                CHANGE country country VARCHAR(3) NOT NULL,
                CHANGE city city VARCHAR(255) NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_property
                DROP
                FOREIGN KEY FK_4837837740168F46"
        );
    }
}
