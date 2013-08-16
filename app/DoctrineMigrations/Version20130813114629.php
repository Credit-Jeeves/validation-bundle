<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130813114629 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_checkout_heartland (id BIGINT AUTO_INCREMENT NOT NULL,
                order_id BIGINT DEFAULT NULL,
                status ENUM('pending','returned','processed','cancelled')
                    COMMENT '(DC2Type:CheckoutStatus)' DEFAULT 'pending' NOT NULL,
                amount INT NOT NULL,
                created_at DATETIME NOT NULL,
                UNIQUE INDEX UNIQ_A1CC46997BA3972D (order_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_checkout_heartland
                ADD CONSTRAINT FK_A1CC46997BA3972D
                FOREIGN KEY (order_id)
                REFERENCES cj_order (id)"
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
            "DROP TABLE rj_checkout_heartland"
        );
    }
}
