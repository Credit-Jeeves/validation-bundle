<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150619124500 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP TABLE rj_aci_collect_pay_settings"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "CREATE TABLE rj_aci_collect_pay_settings (id INT AUTO_INCREMENT NOT NULL,
                group_id BIGINT DEFAULT NULL,
                business_id INT NOT NULL,
                UNIQUE INDEX UNIQ_50CD37FCFE54D947 (group_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
    }
}
