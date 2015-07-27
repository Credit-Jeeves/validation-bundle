<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150708103731 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "CREATE TABLE rj_aci_import_profile_map (
                id INT AUTO_INCREMENT NOT NULL,
                user_id BIGINT DEFAULT NULL,
                group_id BIGINT DEFAULT NULL,
                UNIQUE INDEX UNIQ_D2F20E31A76ED395 (user_id),
                UNIQUE INDEX UNIQ_D2F20E31FE54D947 (group_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_aci_import_profile_map
                ADD CONSTRAINT FK_D2F20E31A76ED395
                FOREIGN KEY (user_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_aci_import_profile_map
                ADD CONSTRAINT FK_D2F20E31FE54D947
                FOREIGN KEY (group_id)
                REFERENCES rj_group (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP TABLE rj_aci_import_profile_map"
        );
    }
}
