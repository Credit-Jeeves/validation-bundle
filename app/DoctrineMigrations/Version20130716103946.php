<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130716103946 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_permission (agent_id BIGINT NOT NULL,
                group_id BIGINT NOT NULL,
                INDEX IDX_FF3CD81A3414710B (agent_id),
                INDEX IDX_FF3CD81AFE54D947 (group_id),
                PRIMARY KEY(agent_id,
                group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_permission
                ADD CONSTRAINT FK_FF3CD81A3414710B
                FOREIGN KEY (agent_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_permission
                ADD CONSTRAINT FK_FF3CD81AFE54D947
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
            "DROP TABLE rj_permission"
        );
    }
}
