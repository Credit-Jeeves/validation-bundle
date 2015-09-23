<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150904141638 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE cj_user
                CHANGE score_changed_notification email_notification TINYINT(1) DEFAULT '1'"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE cj_user
                CHANGE email_notification score_changed_notification TINYINT(1) DEFAULT '1'"
        );
    }
}
