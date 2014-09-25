<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140824005351 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_payment
                DROP
                FOREIGN KEY FK_A4398CF0AE9DDE6F"
        );
        $this->addSql(
            "ALTER TABLE rj_payment
                ADD CONSTRAINT FK_A4398CF0AE9DDE6F
                FOREIGN KEY (payment_account_id)
                REFERENCES rj_payment_account (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account_deposit_account
                DROP
                FOREIGN KEY FK_3171B7F4AE9DDE6F"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account_deposit_account
                ADD CONSTRAINT FK_2E90AACF6E60BC73
                FOREIGN KEY (deposit_account_id)
                REFERENCES rj_deposit_account (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account_deposit_account
                DROP
                FOREIGN KEY FK_3171B7F46E60BC73"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account_deposit_account
                ADD CONSTRAINT FK_2E90AACFAE9DDE6F
                FOREIGN KEY (payment_account_id)
                REFERENCES rj_payment_account (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE rj_user_settings
                DROP
                FOREIGN KEY FK_EA6F98F69305140F;"
        );
        $this->addSql(
            "ALTER TABLE rj_user_settings
                ADD CONSTRAINT FK_EA6F98F69305140F
                FOREIGN KEY (credit_track_payment_account_id)
                REFERENCES rj_payment_account (id) ON DELETE SET NULL"
        );
        $this->addSql(
            "ALTER TABLE jms_job_related_entities
                DROP
                FOREIGN KEY FK_E956F4E29305140F"
        );
        $this->addSql(
            "ALTER TABLE jms_job_related_entities
                ADD CONSTRAINT FK_E956F4E29305140F
                FOREIGN KEY (credit_track_payment_account_id)
                REFERENCES rj_payment_account (id) ON DELETE SET NULL"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_payment
                DROP
                FOREIGN KEY FK_A4398CF0AE9DDE6F"
        );
        $this->addSql(
            "ALTER TABLE rj_payment
                ADD CONSTRAINT FK_A4398CF0AE9DDE6F
                FOREIGN KEY (payment_account_id)
                REFERENCES rj_payment_account (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account_deposit_account
                DROP
                FOREIGN KEY FK_2E90AACFAE9DDE6F"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account_deposit_account
                ADD CONSTRAINT FK_3171B7F46E60BC73
                FOREIGN KEY (payment_account_id)
                REFERENCES rj_payment_account (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account_deposit_account
                DROP
                FOREIGN KEY FK_2E90AACF6E60BC73"
        );
        $this->addSql(
            "ALTER TABLE rj_payment_account_deposit_account
                ADD CONSTRAINT FK_3171B7F4AE9DDE6F
                FOREIGN KEY (deposit_account_id)
                REFERENCES rj_deposit_account (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_user_settings
                DROP
                FOREIGN KEY FK_EA6F98F69305140F;"
        );
        $this->addSql(
            "ALTER TABLE rj_user_settings
                ADD CONSTRAINT FK_EA6F98F69305140F
                FOREIGN KEY (credit_track_payment_account_id)
                REFERENCES rj_payment_account (id)"
        );
        $this->addSql(
            "ALTER TABLE jms_job_related_entities
                DROP
                FOREIGN KEY FK_E956F4E29305140F"
        );
        $this->addSql(
            "ALTER TABLE jms_job_related_entities
                ADD CONSTRAINT FK_E956F4E29305140F
                FOREIGN KEY (credit_track_payment_account_id)
                REFERENCES rj_payment_account (id)"
        );
    }
}
