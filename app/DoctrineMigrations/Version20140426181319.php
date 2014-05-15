<?php

namespace Application\Migrations;

use CreditJeeves\DataBundle\Enum\OperationType;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use RentJeeves\CoreBundle\DateTime;
use PDO;

class Version20140426181319 extends AbstractMigration
{
    /**
     * @see https://credit.atlassian.net/browse/RT-424
     */
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $sql = "SELECT o.days_late AS days_late,
            op.id AS operation_id, op.created_at AS created_at, op.type AS op_type
            c.due_date AS contract_due_date
        FROM `cj_order` AS o
        INNER JOIN cj_operation AS op ON op.order_id = o.id
        LEFT JOIN rj_contract AS c ON op.contract_id = c.id
        ORDER BY `o`.id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $date = new DateTime($row['created_at']);
//            // don't you need to reverse sign of days_late below?
//            if ($row['days_late']) {
//                $modify = ($row['days_late'] * -1) . " days";
//                $date->modify($modify);
//            }
            if (OperationType::RENT == $row['op_type'] && $date->format('d') >= 22) {
                //snap to next month on 1st
                $date->modify('next month');
            }
            if ($row['contract_due_date']) {
                $date->setDate(null, null, $row['contract_due_date']);
            }
            $paidFor = $date->format('Y-m-d');
            $this->addSql("UPDATE `cj_operation` SET paid_for = '{$paidFor}' WHERE id = {$row['operation_id']}");
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
