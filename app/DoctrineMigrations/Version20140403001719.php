<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use DateTime;
use PDO;

class Version20140403001719 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $sql = "SELECT o.days_late AS days_late, op.id AS operation_id, op.created_at AS created_at
        FROM `cj_order` AS o
        INNER JOIN cj_operation AS op ON op.order_id = o.id
        ORDER BY `o`.id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $oldRow = array('cj_operation_id' => null);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $date = new DateTime($row['created_at']);
            if ($row['days_late']) {
                $modify = $row['days_late'] . " days";
                $date->modify($modify);
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
