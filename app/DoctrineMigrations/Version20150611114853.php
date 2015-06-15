<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150611114853 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE rj_transaction_outbound
                ADD type ENUM('deposit','reversal')
                    COMMENT '(DC2Type:OutboundTransactionType)' NOT NULL,
                ADD reversal_description VARCHAR(255) DEFAULT NULL,
                ADD batch_id INT DEFAULT NULL,
                ADD batch_close_date DATETIME DEFAULT NULL,
                DROP status"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_transaction_outbound
                ADD status VARCHAR(255) NOT NULL,
                DROP type,
                DROP reversal_description,
                DROP batch_id,
                DROP batch_close_date"
        );
    }
}
