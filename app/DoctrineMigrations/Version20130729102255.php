<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130729102255 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_alert (id INT AUTO_INCREMENT NOT NULL,
                user_id BIGINT DEFAULT NULL,
                message VARCHAR(255) NOT NULL,
                INDEX IDX_889651D1A76ED395 (user_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_alert
                ADD CONSTRAINT FK_889651D1A76ED395
                FOREIGN KEY (user_id)
                REFERENCES cj_user (id)"
        );

        $this->addSql("ALTER TABLE  `rj_contract` CHANGE  `status`  
                      `status` ENUM('pending','approved','finished','current','active')
                      CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL 
                      DEFAULT 'pending' COMMENT '(DC2Type:ContractStatus)'");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "DROP TABLE rj_alert"
        );


        $this->addSql("ALTER TABLE  `rj_contract` CHANGE  `status`  
                      `status` ENUM('pending', 'approved', 'finished', 'paid')
                      CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL 
                      DEFAULT 'pending' COMMENT '(DC2Type:ContractStatus)'");
    }
}
