<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130718122144 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE rj_unit
                ADD holding_id BIGINT DEFAULT NULL,
                ADD group_id BIGINT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_unit
                ADD CONSTRAINT FK_848B9156CD5FBA3
                FOREIGN KEY (holding_id)
                REFERENCES cj_holding (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_unit
                ADD CONSTRAINT FK_848B915FE54D947
                FOREIGN KEY (group_id)
                REFERENCES cj_account_group (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_848B9156CD5FBA3 ON rj_unit (holding_id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_848B915FE54D947 ON rj_unit (group_id)"
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
            "ALTER TABLE rj_unit
                DROP
                FOREIGN KEY FK_848B9156CD5FBA3"
        );
        $this->addSql(
            "ALTER TABLE rj_unit
                DROP
                FOREIGN KEY FK_848B915FE54D947"
        );
        $this->addSql(
            "DROP INDEX IDX_848B9156CD5FBA3 ON rj_unit"
        );
        $this->addSql(
            "DROP INDEX IDX_848B915FE54D947 ON rj_unit"
        );
        $this->addSql(
            "ALTER TABLE rj_unit
                DROP holding_id,
                DROP group_id"
        );
    }
}
