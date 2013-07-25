<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130725123102 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_payment (id BIGINT AUTO_INCREMENT NOT NULL,
                tenant_id BIGINT DEFAULT NULL,
                contract_id BIGINT DEFAULT NULL,
                amount INT NOT NULL,
                status ENUM('pending','complete','error','cancelled')
                    COMMENT '(DC2Type:PaymentStatus)' DEFAULT 'pending' NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                INDEX IDX_A4398CF09033212A (tenant_id),
                INDEX IDX_A4398CF02576E0FD (contract_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_payment
                ADD CONSTRAINT FK_A4398CF09033212A
                FOREIGN KEY (tenant_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_payment
                ADD CONSTRAINT FK_A4398CF02576E0FD
                FOREIGN KEY (contract_id)
                REFERENCES rj_contract (id)"
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
            "DROP TABLE rj_payment"
        );
    }
}
