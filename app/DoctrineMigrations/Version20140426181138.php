<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use PDO;
use DateTime;

/**
 * Fill new field paid_for in rj_payment table
 */
class Version20140426181138 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $sql = "SELECT p.id AS payment_id, c.due_date, p.start_year, p.start_month, p.due_date AS start_day
        FROM `rj_payment` AS p
        INNER JOIN rj_contract AS c ON p.contract_id = c.id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $now = new DateTime();
            $startAtDay = cal_days_in_month(CAL_GREGORIAN, $row['start_month'], $row['start_year']);
            $startAt = new DateTime(
                $row['start_year'] . '-' . $row['start_month'] . '-' .
                ($startAtDay<$row['start_day']?$startAtDay:$row['start_day']))
            ;
            if ($now->format('Y-m-d') < $startAt->format('Y-m-d')) {
                $now = $startAt;
            }
            if ($now->format('j') > $row['due_date']) {
                $now->modify('+1 month');
            }
            $now->setDate($now->format('Y'), $now->format('n'), $row['due_date']);
            $paidFor = $now->format('Y-m-d');
            $this->addSql("UPDATE `rj_payment` SET paid_for = '{$paidFor}' WHERE id = {$row['payment_id']}");
        }
    }

    public function down(Schema $schema)
    {
    }
}
