<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140405200815 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "INSERT INTO `cj_account_group`
            (`name`, `code`, `fee_type`, `type`, `created_at`, `updated_at`)
            VALUES
            ('RentTrackCorp', 'RentTrackCorp', 'flat', 'generic', '2014-04-05 20:13:25', '2014-04-05 20:13:25')"
        );
        $this->addSql(
            "INSERT INTO `rj_deposit_account` SET group_id = (
                SELECT `id` FROM `cj_account_group` WHERE `code`='RentTrackCorp'
              ),
            merchant_name = 'RentTrackCorp',
            status = 'complete'"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "DELETE FROM `rj_deposit_account` WHERE `group_id`=(
              SELECT `id` FROM `cj_account_group` WHERE `code`='RentTrackCorp'
            )"
        );
        $this->addSql(
            "DELETE FROM `cj_account_group` WHERE `code`='RentTrackCorp'"
        );
    }
}
