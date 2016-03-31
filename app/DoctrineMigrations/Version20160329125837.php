<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160329125837 extends AbstractMigration
{
    public function up(Schema $schema)
    {
            $this->addSql(
                "CREATE TABLE rj_profitstars_transaction (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    order_id BIGINT(20) NOT NULL,
                    transaction_number VARCHAR(255) NOT NULL,
                    created_at DATETIME NOT NULL,
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
            );
            $this->addSql(
                "ALTER TABLE rj_profitstars_transaction
                    ADD CONSTRAINT rj_profitstars_transaction_order_id_fk
                    FOREIGN KEY (order_id)
                    REFERENCES cj_order (id)"
                );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "DROP TABLE rj_profitstars_transaction"
        );
    }
}
