<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150613201450 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "CREATE TABLE rj_aci_collect_pay_group_profile (id INT AUTO_INCREMENT NOT NULL,
                group_id BIGINT DEFAULT NULL,
                profile_id INT NOT NULL,
                created_at DATETIME NOT NULL,
                UNIQUE INDEX UNIQ_FD65ED2CFE54D947 (group_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_aci_collect_pay_group_profile
                ADD CONSTRAINT FK_FD65ED2CFE54D947
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
            "DROP TABLE rj_aci_collect_pay_group_profile"
        );
    }
}
