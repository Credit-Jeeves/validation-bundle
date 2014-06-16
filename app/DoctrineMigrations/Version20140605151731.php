<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140605151731 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                ADD group_id BIGINT NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                ADD CONSTRAINT FK_23991718FE54D947
                FOREIGN KEY (group_id)
                REFERENCES cj_account_group (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_23991718FE54D947 ON rj_contract_waiting (group_id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                DROP
                FOREIGN KEY FK_23991718FE54D947"
        );
        $this->addSql(
            "DROP INDEX IDX_23991718FE54D947 ON rj_contract_waiting"
        );
        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                DROP group_id"
        );
    }
}
