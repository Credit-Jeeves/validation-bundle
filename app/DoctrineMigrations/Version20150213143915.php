<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150213143915 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_payment_batch_mapping (id BIGINT AUTO_INCREMENT NOT NULL,
                payment_batch_id VARCHAR(255) NOT NULL,
                accounting_batch_id VARCHAR(255) NOT NULL,
                status ENUM('opened','closed')
                    COMMENT '(DC2Type:PaymentBatchStatus)' DEFAULT 'opened' NOT NULL,
                accounting_package_type ENUM('none','yardi voyager','resman')
                    COMMENT '(DC2Type:ApiIntegrationType)' NOT NULL,
                external_property_id VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL,
                closed_at DATETIME DEFAULT NULL,
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "DROP TABLE rj_payment_batch_mapping"
        );
    }
}
