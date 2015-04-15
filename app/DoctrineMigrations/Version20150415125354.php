<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150415125354 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "RENAME TABLE rj_checkout_heartland TO rj_transaction"
        );

        $this->addSql(
            "ALTER TABLE rj_transaction CHANGE amount amount DECIMAL( 10, 2 ) NOT NULL DEFAULT 0.00"
        );

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_transaction CHANGE amount amount DECIMAL( 10, 0 ) NULL DEFAULT NULL"
        );

        $this->addSql(
            "RENAME TABLE rj_transaction TO rj_checkout_heartland"
        );
    }
}
