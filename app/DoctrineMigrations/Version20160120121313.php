<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160120121313 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "CREATE TABLE rj_profitstars_registered_contracts (id BIGINT AUTO_INCREMENT NOT NULL,
                contract_id BIGINT NOT NULL,
                location_id VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX IDX_5FE9830D2576E0FD (contract_id),
                UNIQUE INDEX unique_index_constraint (contract_id,
                location_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_profitstars_registered_contracts
                ADD CONSTRAINT FK_5FE9830D2576E0FD
                FOREIGN KEY (contract_id)
                REFERENCES rj_contract (id)"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "DROP TABLE rj_profitstars_registered_contracts"
        );
    }
}
