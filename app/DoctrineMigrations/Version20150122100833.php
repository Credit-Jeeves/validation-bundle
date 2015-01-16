<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150122100833 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE rj_property_mapping
                DROP INDEX UNIQ_5339818C6CD5FBA3,
                ADD INDEX IDX_5339818C6CD5FBA3 (holding_id)"
        );

        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                CHANGE yardi_payment_accepted payment_accepted  ENUM('0','1','2')
                    COMMENT '(DC2Type:PaymentAccepted)' DEFAULT '0' NOT NULL"
        );

        $this->addSql(
            "ALTER TABLE rj_contract
                CHANGE yardi_payment_accepted payment_accepted ENUM('0','1','2')
                    COMMENT '(DC2Type:PaymentAccepted)' DEFAULT '0' NOT NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_property_mapping
                DROP INDEX IDX_5339818C6CD5FBA3,
                ADD UNIQUE INDEX UNIQ_5339818C6CD5FBA3 (holding_id)"
        );

        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                CHANGE payment_accepted yardi_payment_accepted ENUM('0','1','2')
                    COMMENT '(DC2Type:YardiPaymentAccepted)' DEFAULT '0' NOT NULL"
        );

        $this->addSql(
            "ALTER TABLE rj_contract
                CHANGE payment_accepted yardi_payment_accepted ENUM('0','1','2')
                    COMMENT '(DC2Type:YardiPaymentAccepted)' DEFAULT '0' NOT NULL"
        );
    }
}
