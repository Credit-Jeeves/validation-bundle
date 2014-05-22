<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140522011821 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE `rj_user_settings`
             ADD COLUMN `credit_track_payment_account_id` bigint(20) DEFAULT NULL;");
        $this->addSql(
            "ALTER TABLE `rj_user_settings`
             ADD CONSTRAINT `FK_EA6F98F69305140F` FOREIGN KEY (`credit_track_payment_account_id`) REFERENCES `rj_payment_account` (`id`);");
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE `rj_user_settings`
             DROP FOREIGN KEY `FK_EA6F98F69305140F`;");
        $this->addSql(
            "ALTER TABLE `rj_user_settings`
             DROP COLUMN `credit_track_payment_account_id`;");
    }
}
