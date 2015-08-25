<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150730135712 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE cj_order
                ADD payment_account_id BIGINT DEFAULT NULL,
                ADD deposit_account_id INT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE cj_order
                ADD CONSTRAINT FK_DA53B53DAE9DDE6F
                FOREIGN KEY (payment_account_id)
                REFERENCES rj_payment_account (id)"
        );
        $this->addSql(
            "ALTER TABLE cj_order
                ADD CONSTRAINT FK_DA53B53D6E60BC73
                FOREIGN KEY (deposit_account_id)
                REFERENCES rj_deposit_account (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_DA53B53DAE9DDE6F ON cj_order (payment_account_id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_DA53B53D6E60BC73 ON cj_order (deposit_account_id)"
        );
        $this->addSql(
            "ALTER TABLE rj_payment
                ADD deposit_account_id INT DEFAULT NULL"
        );
        $this->addSql(
            "ALTER TABLE rj_payment
                ADD CONSTRAINT FK_A4398CF06E60BC73
                FOREIGN KEY (deposit_account_id)
                REFERENCES rj_deposit_account (id)"
        );
        $this->addSql(
            "CREATE INDEX IDX_A4398CF06E60BC73 ON rj_payment (deposit_account_id)"
        );
        $this->addSql(
            "UPDATE rj_payment p
                INNER JOIN rj_payment_account pa on (pa.id = p.payment_account_id)
                INNER JOIN rj_contract c on (c.id = p.contract_id)
                INNER JOIN rj_group g on (c.group_id = g.id)
                INNER JOIN rj_deposit_account da on (
                    da.group_id = g.id and da.type = 'rent' and da.payment_processor = pa.payment_processor
                )
            SET p.deposit_account_id = da.id"
        );
        $this->addSql(
            "UPDATE cj_order o
                INNER JOIN rj_transaction t on (o.id = t.order_id and t.status = 'complete' and t.is_successful = 1)
            SET o.payment_account_id = t.payment_account_id"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE cj_order
                DROP
                FOREIGN KEY FK_DA53B53DAE9DDE6F"
        );
        $this->addSql(
            "ALTER TABLE cj_order
                DROP
                FOREIGN KEY FK_DA53B53D6E60BC73"
        );
        $this->addSql(
            "DROP INDEX IDX_DA53B53DAE9DDE6F ON cj_order"
        );
        $this->addSql(
            "DROP INDEX IDX_DA53B53D6E60BC73 ON cj_order"
        );
        $this->addSql(
            "ALTER TABLE cj_order
                DROP payment_account_id,
                DROP deposit_account_id"
        );
        $this->addSql(
            "ALTER TABLE rj_payment
                DROP
                FOREIGN KEY FK_A4398CF06E60BC73"
        );
        $this->addSql(
            "DROP INDEX IDX_A4398CF06E60BC73 ON rj_payment"
        );
        $this->addSql(
            "ALTER TABLE rj_payment
                DROP deposit_account_id"
        );
    }
}
