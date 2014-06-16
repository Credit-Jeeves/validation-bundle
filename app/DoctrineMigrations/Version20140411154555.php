<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140411154555 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_contract_waiting (id BIGINT AUTO_INCREMENT NOT NULL,
                unit_id BIGINT NOT NULL,
                rent NUMERIC(10,
                2) NOT NULL,
                resident_id VARCHAR(128) NOT NULL,
                imported_balance NUMERIC(10,
                2) DEFAULT '0.00' NOT NULL,
                start_at DATE NOT NULL,
                finish_at DATE DEFAULT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                INDEX IDX_23991718F8BD700D (unit_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                ADD CONSTRAINT FK_23991718F8BD700D
                FOREIGN KEY (unit_id)
                REFERENCES rj_unit (id)"
        );

    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "DROP TABLE rj_contract_waiting"
        );
    }
}
