<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20131210125307 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_billing_account
                DROP INDEX UNIQ_6D16C91BFE54D947,
                ADD INDEX IDX_6D16C91BFE54D947 (group_id)"
        );
        $this->addSql(
            "ALTER TABLE rj_billing_account
                ADD active TINYINT(1) DEFAULT '0' NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_billing_account
                DROP INDEX IDX_6D16C91BFE54D947,
                ADD UNIQUE INDEX UNIQ_6D16C91BFE54D947 (group_id)"
        );
        $this->addSql(
            "ALTER TABLE rj_billing_account
                DROP active"
        );
    }
}
