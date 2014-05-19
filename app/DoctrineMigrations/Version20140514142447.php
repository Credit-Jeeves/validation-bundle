<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use RentJeeves\DataBundle\Entity\Unit;

class Version20140514142447 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        // Set is_single = 1 to properties that have only one unit named '1' or having empty name
        $update = $this->connection->prepare(
            "UPDATE rj_property AS a
                INNER JOIN (
                    SELECT p.id, u.name
                    FROM rj_property p
                    INNER JOIN rj_unit u on p.id = u.property_id
                    GROUP BY u.property_id
                    HAVING count(u.id) = 1 and (u.name = '1' or u.name = '')
                ) AS b ON b.id = a.id

                SET a.is_single = '1'"
        );
        $update->execute();

        $propertyName = Unit::SINGLE_PROPERTY_UNIT_NAME;
        // Set new name to standalone property units
        $update2 = $this->connection->prepare(
            "UPDATE rj_unit u
              INNER JOIN rj_property p ON (u.property_id = p.id AND p.is_single = 1)
              SET u.name = '{$propertyName}'"
        );
        $update2->execute();

        // select contracts with no unit
        $sql = "
            select c.id, c.property_id, p.is_single, count(u.id) as count_units, u.id as unit_id
            from rj_contract c inner join rj_property p on c.property_id = p.id
            left join rj_unit u on p.id = u.property_id
            where c.unit_id is null
            and c.status not in ('deleted')
            group by c.id, c.property_id
            having count_units <= 1";

        $select = $this->connection->prepare($sql);
        $select->execute();

        $processedProperties = array();
        while ($row = $select->fetch(\PDO::FETCH_ASSOC)) {
            $propertyId = $row['property_id'];
            $contractId = $row['id'];
            $singleUnit = $row['unit_id'];

            // if property has already been processed, we should only update the contract
            if (array_key_exists($propertyId, $processedProperties)) {
                $propertyUnit = $processedProperties[$propertyId];
                $updateContract = $this->connection->prepare(
                    "UPDATE rj_contract
                     SET unit_id = '{$propertyUnit}'
                     WHERE id = '{$contractId}'"
                );
                $updateContract->execute();
                continue;
            }

            // if property has a contract but has no units, we count it as standalone
            if ($row['count_units'] == 0) {
                $stmt = $this->connection->prepare(
                    "UPDATE rj_property
                     SET is_single = '1'
                     WHERE id = '{$propertyId}'"
                );
                $stmt->execute();

                $stmt2 = $this->connection->prepare("
                    INSERT INTO rj_unit
                    SET `property_id` = '{$propertyId}',
                        `name` = '{$propertyName}',
                        `created_at` = now(),
                        `updated_at` = now()");
                $stmt2->execute();

                $unitId = $this->connection->lastInsertId();

                $stmt3 = $this->connection->prepare(
                    "UPDATE rj_contract
                     SET unit_id = '{$unitId}'
                     WHERE id = '{$contractId}'"
                );
                $stmt3->execute();
                $processedProperties[$propertyId] = $unitId;
            }

            if ($row['count_units'] == 1 && $row['is_single'] == 1) {
                $stmt4 = $this->connection->prepare(
                    "UPDATE rj_contract
                     SET unit_id = '{$singleUnit}'
                     WHERE id = '{$contractId}'"
                );
                $stmt4->execute();
                $processedProperties[$propertyId] = $singleUnit;
            }
        }
    }

    public function down(Schema $schema)
    {

    }
}
