<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131126140347 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE `rj_user_settings` (
              `id` bigint(20) NOT NULL AUTO_INCREMENT,
              `user_id` bigint(20) NOT NULL,
              `is_base_order_report` tinyint(1) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `UNIQ_EA6F98F6A76ED395` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;"
        );
        $this->addSql(
            "ALTER TABLE `rj_user_settings`
            ADD CONSTRAINT `FK_EA6F98F6A76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`);"
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
            "DROP TABLE rj_user_settings"
        );
    }
}
