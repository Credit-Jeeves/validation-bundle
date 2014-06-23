<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use RentJeeves\CoreBundle\DateTime;
use PDO;

class Version20140618141427 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );



        $sql = "SELECT op.id, o.created_at, o.sum, c.due_date AS contract_due_date, c.start_at AS contract_start, o.sum
            FROM `cj_operation` AS op
            INNER JOIN cj_order AS o ON op.order_id = o.id
            INNER JOIN rj_contract AS c ON op.contract_id = c.id
            WHERE DATE(o.created_at) <> DATE(op.created_at)";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $date = new DateTime($row['created_at']);
            if ($date->format('d') >= 22) {
                //snap to next month on 1st
                $date->modify('next month');
            }
            if ($row['contract_due_date']) {
                $date->setDate(null, null, $row['contract_due_date']);
            }
            if ($date->format('Y-m-d') < $row['contract_start']) {
                $contractStart = new DateTime($row['contract_start']);
                $date->setDate(null, $contractStart->format('n'), null);
            }
            $paidFor = $date->format('Y-m-d');
            $this->addSql("UPDATE `cj_operation` SET paid_for = '{$paidFor}', created_at = '{$row['created_at']}',
              amount = {$row['sum']}
              WHERE id = {$row['id']}");
        }
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
    }
}
