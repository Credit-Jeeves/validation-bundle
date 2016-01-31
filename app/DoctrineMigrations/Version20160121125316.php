<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160121125316 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_holding
            CHANGE COLUMN `api_integration_type` `accounting_system`
            enum('none','yardi voyager','resman','mri','amsi', 'mri boston post',
            'promas','yardi genesis', 'yardi genesis v2')
            COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:AccountingSystem)'"
        );

        $this->addSql(
            "ALTER TABLE rj_payment_batch_mapping
            CHANGE COLUMN `accounting_package_type` `accounting_package_type`
            enum('none','yardi voyager','resman','mri','amsi', 'mri boston post',
            'promas','yardi genesis', 'yardi genesis v2')
            COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:AccountingSystem)'"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_holding
            CHANGE COLUMN  `accounting_system` `api_integration_type`
            enum('none','yardi voyager','resman','mri','amsi')
            COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:ApiIntegrationType)'"
        );

        $this->addSql(
            "ALTER TABLE rj_payment_batch_mapping
            CHANGE COLUMN `accounting_package_type` `accounting_package_type`
            enum('none','yardi voyager','resman','mri','amsi')
            COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:ApiIntegrationType)'"
        );
    }
}
