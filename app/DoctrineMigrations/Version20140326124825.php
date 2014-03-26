<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140326124825 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_contract_history (id BIGINT AUTO_INCREMENT NOT NULL,
                object_id BIGINT DEFAULT NULL,
                editor_id BIGINT DEFAULT NULL,
                status ENUM('pending','invite','approved','current','finished','deleted')
                    COMMENT '(DC2Type:ContractStatus)' DEFAULT 'pending' NOT NULL,
                rent NUMERIC(10,
                2) DEFAULT NULL,
                uncollected_balance NUMERIC(10,
                2) DEFAULT NULL,
                balance NUMERIC(10,
                2) DEFAULT '0.00' NOT NULL,
                imported_balance NUMERIC(10,
                2) DEFAULT '0.00' NOT NULL,
                paid_to DATE DEFAULT NULL,
                reporting TINYINT(1) DEFAULT '0',
                start_at DATE DEFAULT NULL,
                finish_at DATE DEFAULT NULL,
                logged_at DATETIME NOT NULL,
                action VARCHAR(8) NOT NULL,
                INDEX IDX_6CF9EAFD232D562B (object_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_contract_history
                ADD CONSTRAINT FK_6CF9EAFD232D562B
                FOREIGN KEY (object_id)
                REFERENCES rj_contract (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "DROP TABLE rj_contract_history"
        );
    }
}
