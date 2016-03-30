<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160330141837 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_contract
                CHANGE status status enum('pending',
                'invite','approved','current','finished','deleted','waiting')
                 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending' COMMENT '(DC2Type:ContractStatus)'"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE rj_contract
                CHANGE status status enum('pending',
                'invite','approved','current','finished','deleted'
            ) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending' COMMENT '(DC2Type:ContractStatus)'"
        );
    }
}
