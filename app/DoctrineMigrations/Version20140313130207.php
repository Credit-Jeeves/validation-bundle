<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use PDO;

class Version20140313130207 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $sql = "SELECT ord.*, o.*, op.*, ord.amount AS order_amount, h.amount AS h_amount, op.cj_order_id AS cj_order_id
        FROM `cj_order_operation` AS op
        INNER JOIN cj_operation AS o ON op.cj_operation_id = o.id
        INNER JOIN cj_order AS ord ON op.cj_order_id = ord.id
        LEFT JOIN rj_checkout_heartland AS h ON h.order_id = ord.id
        GROUP BY `cj_order_id`, `cj_operation_id`";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $oldRow = array('cj_operation_id' => null);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $amount = empty($row['order_amount'])?$row['h_amount']:$row['order_amount'];
            if ($oldRow['cj_operation_id'] != $row['cj_operation_id']) {
                $this->addSql("UPDATE cj_operation
                SET `order_id` = {$row['cj_order_id']},
                    `amount` = '{$amount}'
                WHERE `id` = {$row['cj_operation_id']}
                ");
                $oldRow = $row;
            } else {
                $sql = "INSERT cj_operation
                SET
                `order_id` = {$row['cj_order_id']},
                `amount` = {$amount},
                `type` = '{$oldRow['type']}',
                `created_at` = '{$oldRow['created_at']}'
                ";
                if ($oldRow['cj_applicant_report_id']) {
                    $sql .= ",`cj_applicant_report_id` = {$oldRow['cj_applicant_report_id']}";
                }
                if ($oldRow['contract_id']) {
                    $sql .= ",`contract_id` = {$oldRow['contract_id']}";
                }
                if ($oldRow['group_id']) {
                    $sql .= ",`group_id` = {$oldRow['group_id']}";
                }
                $this->addSql($sql);
            }
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
