<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140525115818 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                ADD property_id BIGINT NOT NULL"
        );

        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                ADD CONSTRAINT FK_23991718549213EC
                FOREIGN KEY (property_id)
                REFERENCES rj_property (id)"
        );

        $this->addSql(
            "CREATE INDEX IDX_23991718549213EC ON rj_contract_waiting (property_id)"
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
                FOREIGN KEY FK_23991718549213EC"
        );

        $this->addSql(
            "DROP INDEX IDX_23991718549213EC ON rj_contract_waiting"
        );

        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                DROP property_id"
        );
    }
}
