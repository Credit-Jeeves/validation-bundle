<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use PDO;

class Version20150226112348 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        // select records with non numeric symbols in phone number
        $sql = "SELECT id, phone FROM cj_user where phone REGEXP '[^0-9]+';";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // remove non numeric symbols
            $cleanPhoneNumber = preg_replace('/\D/', '', $row['phone']);
            // set clean phone
            $this->addSql("UPDATE `cj_user` SET phone = '{$cleanPhoneNumber}' WHERE id = {$row['id']}");
        }
    }

    public function down(Schema $schema)
    {
    }
}
