<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130723135420 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_invite (id INT AUTO_INCREMENT NOT NULL,
                user_id BIGINT DEFAULT NULL,
                property_id BIGINT DEFAULT NULL,
                first_name VARCHAR(255) DEFAULT NULL,
                last_name VARCHAR(255) DEFAULT NULL,
                phone VARCHAR(50) DEFAULT NULL,
                email VARCHAR(100) NOT NULL,
                unit VARCHAR(50) DEFAULT NULL,
                UNIQUE INDEX UNIQ_DACA6BA4A76ED395 (user_id),
                INDEX IDX_DACA6BA4549213EC (property_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_invite
                ADD CONSTRAINT FK_DACA6BA4A76ED395
                FOREIGN KEY (user_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_invite
                ADD CONSTRAINT FK_DACA6BA4549213EC
                FOREIGN KEY (property_id)
                REFERENCES rj_property (id)"
        );

        $this->addSql(
            "ALTER TABLE rj_property
                ADD google_reference VARCHAR(255) DEFAULT NULL"
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
            "DROP TABLE rj_invite"
        );
        
        $this->addSql(
            "ALTER TABLE rj_property
                DROP google_reference"
        );
    }
}
