<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140831023423 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql("ALTER TABLE `cj_settings` CHANGE `pidkiq_password` `precise_id_user_pwd` LONGTEXT");
        $this->addSql("ALTER TABLE `cj_settings` CHANGE `pidkiq_eai` `precise_id_eai` LONGTEXT");
        $this->addSql("ALTER TABLE `cj_settings` CHANGE `net_connect_password` `credit_profile_user_pwd` LONGTEXT");
        $this->addSql("ALTER TABLE `cj_settings` CHANGE `net_connect_eai` `credit_profile_eai` LONGTEXT");
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
    }
}
