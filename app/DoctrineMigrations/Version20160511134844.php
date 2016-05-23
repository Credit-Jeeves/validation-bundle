<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160511134844 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE cj_holding
                CHANGE accounting_system accounting_system
                enum('none','amsi','mri','mri bostonpost','promas','resman','yardi voyager','yardi genesis',
                'yardi genesis v2', 'rent manager')
                COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none' COMMENT '(DC2Type:AccountingSystem)'"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_batch_mapping
                CHANGE accounting_package_type accounting_package_type
                enum('none','amsi','mri','mri bostonpost','promas','resman','yardi voyager','yardi genesis',
                'yardi genesis v2', 'rent manager')
                COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none' COMMENT '(DC2Type:AccountingSystem)'"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            "ALTER TABLE cj_holding
                CHANGE accounting_system accounting_system
                enum('none','amsi','mri','mri bostonpost','promas','resman','yardi voyager','yardi genesis',
                'yardi genesis v2')
                COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none' COMMENT '(DC2Type:AccountingSystem)'"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_batch_mapping
                CHANGE accounting_package_type accounting_package_type
                enum('none','amsi','mri','mri bostonpost','promas','resman','yardi voyager','yardi genesis',
                'yardi genesis v2')
                COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none' COMMENT '(DC2Type:AccountingSystem)'"
        );
    }
}
