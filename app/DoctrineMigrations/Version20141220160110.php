<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141220160110 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE rj_invite
                DROP
                FOREIGN KEY FK_DACA6BA4F8BD700D"
        );
        $this->addSql(
            "DROP INDEX IDX_DACA6BA4F8BD700D ON rj_invite"
        );
        $this->addSql(
            "ALTER TABLE rj_invite
                DROP unit_id"
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
            "ALTER TABLE rj_invite
                ADD unit_id BIGINT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_invite
                ADD CONSTRAINT FK_DACA6BA4F8BD700D
                FOREIGN KEY (unit_id)
                REFERENCES rj_unit (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_DACA6BA4F8BD700D ON rj_invite (unit_id)"
        );
    }
}
