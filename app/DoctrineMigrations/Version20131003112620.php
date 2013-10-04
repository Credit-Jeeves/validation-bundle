<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131003112620 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE rj_contract
                CHANGE COLUMN `status` `status` ENUM('pending','invite','approved','current','finished', 'deleted') 
                CHARACTER SET 'utf8' 
                COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'pending' 
                COMMENT '(DC2Type:ContractStatus)"
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
            "ALTER TABLE rj_contract
                CHANGE COLUMN `status` `status` ENUM('pending','approved','finished')
                CHARACTER SET 'utf8'
                COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'pending'
                COMMENT '(DC2Type:ContractStatus)"
        );
    }
}
