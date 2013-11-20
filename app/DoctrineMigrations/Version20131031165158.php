<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131031165158 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE auth_code
                DROP
                FOREIGN KEY FK_5933D02C19EB6921"
        );
        $this->addSql(
            "DROP INDEX IDX_5933D02C19EB6921 ON auth_code"
        );
        $this->addSql(
            "ALTER TABLE auth_code
                DROP client_id"
        );

        $this->addSql(
            "ALTER TABLE access_token
                DROP
                FOREIGN KEY FK_B6A2DD6819EB6921"
        );
        $this->addSql(
            "DROP INDEX IDX_B6A2DD6819EB6921 ON access_token"
        );
        $this->addSql(
            "ALTER TABLE access_token
                DROP client_id"
        );

        $this->addSql(
            "ALTER TABLE refresh_token
                DROP
                FOREIGN KEY FK_C74F219519EB6921"
        );
        $this->addSql(
            "DROP INDEX IDX_C74F219519EB6921 ON refresh_token"
        );
        $this->addSql(
            "ALTER TABLE refresh_token
                DROP client_id"
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
            "ALTER TABLE access_token
                ADD client_id INT NOT NULL"
        );
        
        $this->addSql(
            "ALTER TABLE access_token
                ADD CONSTRAINT FK_B6A2DD6819EB6921
                FOREIGN KEY (client_id)
                REFERENCES client (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_B6A2DD6819EB6921 ON access_token (client_id)"
        );

        $this->addSql(
            "ALTER TABLE auth_code
                ADD client_id INT NOT NULL"
        );

        $this->addSql(
            "ALTER TABLE auth_code
                ADD CONSTRAINT FK_5933D02C19EB6921
                FOREIGN KEY (client_id)
                REFERENCES client (id)"
        );

        $this->addSql(
            "CREATE INDEX IDX_5933D02C19EB6921 ON auth_code (client_id)"
        );

        $this->addSql(
            "ALTER TABLE refresh_token
                ADD client_id INT NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE refresh_token
                ADD CONSTRAINT FK_C74F219519EB6921
                FOREIGN KEY (client_id)
                REFERENCES client (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_C74F219519EB6921 ON refresh_token (client_id)"
        );
    }
}
