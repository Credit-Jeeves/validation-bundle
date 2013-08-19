<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130816113404 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE cj_login_defense
                CHANGE user_id user_id BIGINT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_login_defense
                ADD CONSTRAINT FK_6C609834A76ED395
                FOREIGN KEY (user_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "CREATE UNIQUE INDEX UNIQ_6C609834A76ED395 ON cj_login_defense (user_id)"
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
            "ALTER TABLE cj_login_defense
                DROP
                FOREIGN KEY FK_6C609834A76ED395"
        );
        $this->addSql(
            "DROP INDEX UNIQ_6C609834A76ED395 ON cj_login_defense"
        );
        $this->addSql(
            "ALTER TABLE cj_login_defense
                CHANGE user_id user_id BIGINT NOT NULL"
        );
    }
}
