<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150603111623 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "CREATE TABLE rj_transaction_outbound (id INT AUTO_INCREMENT NOT NULL,
                order_id BIGINT NOT NULL,
                transaction_id INT NOT NULL,
                status VARCHAR(255) NOT NULL,
                amount NUMERIC(10,
                2) NOT NULL,
                deposit_date DATETIME DEFAULT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                INDEX IDX_DF380F958D9F6D38 (order_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_transaction_outbound
                ADD CONSTRAINT FK_DF380F958D9F6D38
                FOREIGN KEY (order_id)
                REFERENCES cj_order (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "DROP TABLE rj_transaction_outbound"
        );
    }
}
