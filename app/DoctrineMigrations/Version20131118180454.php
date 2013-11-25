<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20131118180454 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_billing_account (id INT AUTO_INCREMENT NOT NULL,
                group_id BIGINT DEFAULT NULL,
                token VARCHAR(255) NOT NULL,
                UNIQUE INDEX UNIQ_6D16C91BFE54D947 (group_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_billing_account
                ADD CONSTRAINT FK_6D16C91BFE54D947
                FOREIGN KEY (group_id)
                REFERENCES cj_account_group (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "DROP TABLE rj_billing_account"
        );
    }
}
