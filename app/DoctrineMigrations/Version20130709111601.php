<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130709111601 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE cj_user
                ADD CONSTRAINT FK_98C9F475FE54D947
                FOREIGN KEY (group_id)
                REFERENCES cj_account_group (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_98C9F475FE54D947 ON cj_user (group_id)"
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
            "ALTER TABLE cj_user
                DROP
                FOREIGN KEY FK_98C9F475FE54D947"
        );
        $this->addSql(
            "DROP INDEX IDX_98C9F475FE54D947 ON cj_user"
        );
    }
}
