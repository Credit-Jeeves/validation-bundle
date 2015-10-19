<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151019112557 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_payment_account
                ADD registered TINYINT(1) DEFAULT '0' NOT NULL,
                MODIFY COLUMN `type` enum('bank','card', 'debit_card')
                COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:PaymentAccountType)'"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_payment_account
                DROP registered,
                MODIFY COLUMN `type` enum('bank','card')
                COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:PaymentAccountType)'"
        );
    }
}
