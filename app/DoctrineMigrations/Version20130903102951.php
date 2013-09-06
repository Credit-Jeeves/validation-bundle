<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130903102951 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE `rj_deposit_account` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `group_id` bigint(20) DEFAULT NULL,
              `merchant_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1"
        );

        $this->addSql(
            "CREATE UNIQUE INDEX UNIQ_7F2B897FE54D947 ON rj_deposit_account (group_id)"
        );

        $this->addSql(
            "ALTER TABLE rj_deposit_account
                ADD CONSTRAINT FK_7F2B897FE54D947
                FOREIGN KEY (group_id)
                REFERENCES cj_account_group (id)"
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
            "DROP TABLE `rj_deposit_account`"
        );

        $this->addSql(
            "ALTER TABLE rj_deposit_account
                DROP
                FOREIGN KEY FK_7F2B897FE54D947"
        );
    }
}
