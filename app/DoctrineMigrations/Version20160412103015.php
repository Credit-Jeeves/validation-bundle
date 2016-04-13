<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160412103015 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE yardi_settings
                ADD payment_type_scanned_check ENUM('cash','check')
                    COMMENT '(DC2Type:PaymentTypeScannedCheck)' DEFAULT 'check' NOT NULL,
                ADD notes_scanned_check LONGTEXT DEFAULT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE yardi_settings
                DROP payment_type_scanned_check,
                DROP notes_scanned_check"
        );
    }
}
