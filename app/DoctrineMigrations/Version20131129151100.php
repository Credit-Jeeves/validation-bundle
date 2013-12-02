<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131129151100 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE cj_operation
                CHANGE amount amount INT NOT NULL"
        );

        $sql = "SELECT cj_operation.id as id, cj_order.amount as amount
                FROM cj_operation
                LEFT JOIN cj_order_operation ON cj_order_operation.cj_operation_id = cj_operation.id
                LEFT JOIN cj_order ON cj_order_operation.cj_order_id = cj_order.id
                WHERE cj_operation.amount IS NULL OR cj_operation.amount <= 0
                GROUP BY cj_order.id
                ORDER BY cj_operation.id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $id = null;
        $amount = 0;
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (is_null($row['amount'])) {
                $row['amount'] = 0;
            }

            if (is_null($id)) {
                $id = $row['id'];
                $amount = $row['amount'];
                continue;
            }

            if ($id !== $row['id']) {
                $this->addSql(
                    "UPDATE  `cj_operation` SET  `amount` =  '{$amount}' WHERE  `cj_operation`.`id` ={$id};"
                );
                $id = $row['id'];
                $amount = $row['amount'];
                continue;
            }

            $amount += $row['amount'];
        }

        if ($id) {
            $this->addSql(
                "UPDATE  `cj_operation` SET  `amount` =  '{$amount}' WHERE  `cj_operation`.`id` ={$id};"
            );
        }
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE cj_operation
                CHANGE amount amount INT DEFAULT NULL"
        );

    }
}
