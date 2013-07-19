<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130719115052 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_contract (id BIGINT AUTO_INCREMENT NOT NULL,
                tenant_id BIGINT DEFAULT NULL,
                holding_id BIGINT DEFAULT NULL,
                group_id BIGINT DEFAULT NULL,
                property_id BIGINT DEFAULT NULL,
                unit_id BIGINT DEFAULT NULL,
                search VARCHAR(255) NOT NULL,
                status ENUM('pending','approved','finished')
                    COMMENT '(DC2Type:ContractStatus)' DEFAULT 'pending' NOT NULL,
                rent INT DEFAULT NULL,
                start_at DATETIME NOT NULL,
                finish_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                INDEX IDX_2A4AB7F09033212A (tenant_id),
                INDEX IDX_2A4AB7F06CD5FBA3 (holding_id),
                INDEX IDX_2A4AB7F0FE54D947 (group_id),
                INDEX IDX_2A4AB7F0549213EC (property_id),
                INDEX IDX_2A4AB7F0F8BD700D (unit_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_contract
                ADD CONSTRAINT FK_2A4AB7F09033212A
                FOREIGN KEY (tenant_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_contract
                ADD CONSTRAINT FK_2A4AB7F06CD5FBA3
                FOREIGN KEY (holding_id)
                REFERENCES cj_holding (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_contract
                ADD CONSTRAINT FK_2A4AB7F0FE54D947
                FOREIGN KEY (group_id)
                REFERENCES cj_account_group (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_contract
                ADD CONSTRAINT FK_2A4AB7F0549213EC
                FOREIGN KEY (property_id)
                REFERENCES rj_property (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_contract
                ADD CONSTRAINT FK_2A4AB7F0F8BD700D
                FOREIGN KEY (unit_id)
                REFERENCES rj_unit (id)"
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
            "DROP TABLE rj_contract"
        );
    }
}
