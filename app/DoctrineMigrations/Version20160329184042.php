<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160329184042 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_group_settings
                ADD allowed_credit_card TINYINT(1) DEFAULT '1' NOT NULL,
                ADD allowed_ach TINYINT(1) DEFAULT '1' NOT NULL"
        );

        $this->addSql(
            "UPDATE rj_group_settings gs SET allowed_credit_card = NOT
              (SELECT disable_credit_card FROM rj_group g WHERE g.id = gs.group_id)"
        );

        $this->addSql(
            "ALTER TABLE rj_group
                DROP disable_credit_card"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE rj_group
                ADD disable_credit_card TINYINT(1) DEFAULT '0' NOT NULL"
        );

        $this->addSql(
            "UPDATE rj_group g SET disable_credit_card = NOT
              (SELECT allowed_credit_card FROM rj_group_settings gs WHERE gs.group_id = g.id)"
        );

        $this->addSql(
            "ALTER TABLE rj_group_settings
                DROP allowed_credit_card,
                DROP allowed_ach"
        );
    }
}
