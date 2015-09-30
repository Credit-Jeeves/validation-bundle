<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151001143614 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE rj_aci_collect_pay_profile_billing (
                id INT AUTO_INCREMENT NOT NULL,
                profile_id INT DEFAULT NULL,
                division_id VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL,
                INDEX IDX_A93E11B7CCFA12B8 (profile_id),
                UNIQUE INDEX profile_billing_unique_constraint (profile_id, division_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_aci_collect_pay_profile_billing
                ADD CONSTRAINT FK_A93E11B7CCFA12B8
                FOREIGN KEY (profile_id)
                REFERENCES rj_aci_collect_pay_user_profile (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP TABLE rj_aci_collect_pay_profile_billing"
        );
    }
}
