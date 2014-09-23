<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140405200815 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $ret = $this->addSql(
            "INSERT INTO `cj_account_group`
            (`name`, `code`, `fee_type`, `type`, `created_at`, `updated_at`)
            VALUES
            ('RentTrackCorp', 'RentTrackCorp', 'flat', 'generic', '2014-04-05 20:13:25', '2014-04-05 20:13:25')"
        );
        $ret = $this->addSql(
            "INSERT INTO `rj_deposit_account` VALUES (NULL, (
              SELECT `id` FROM `cj_account_group` WHERE `code`='RentTrackCorp'
            ), 'RentTrackCorp', NULL, 'complete', NULL, NULL, NULL)"
        );
    }

    public function down(Schema $schema)
    {
        $ret = $this->addSql(
            "DELETE FROM `rj_deposit_account` WHERE `group_id`=(
              SELECT `id` FROM `cj_account_group` WHERE `code`='RentTrackCorp'
            )"
        );
        $this->addSql(
            "DELETE FROM `cj_account_group` WHERE `code`='RentTrackCorp'"
        );
    }
}
