<?php
namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130702012817 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        require_once __DIR__ . '/../../vendor/CreditJeevesSf1/lib/utility/cjEncryptionUtility.class.php';

        $this->addSql('TRUNCATE TABLE  `cj_address`');

        $this->addSql(
            "ALTER TABLE cj_address
                CHANGE address2 number LONGTEXT DEFAULT NULL,
                CHANGE address1 street LONGTEXT NOT NULL,
                ADD area VARCHAR(255) DEFAULT NULL,
                ADD unit LONGTEXT DEFAULT NULL,
                CHANGE zip zip VARCHAR(15) DEFAULT NULL,
                CHANGE country country VARCHAR(3) DEFAULT 'US' NOT NULL,
                CHANGE state district VARCHAR(7) DEFAULT NULL"
        );

        $sql = "SELECT * FROM cj_user";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

          if (empty($row['street_address1'])) {
            continue;
          }

          if (false === \cjEncryptionUtility::decrypt(base64_decode($row['street_address1']))) {
            $row['street_address1'] = base64_encode(\cjEncryptionUtility::encrypt($row['street_address1']));
          }

          $row['unit_no'] = base64_encode(\cjEncryptionUtility::encrypt($row['unit_no']));

          $this->addSql(
            "INSERT INTO `cj_address`
            SET
              `user_id` = '{$row['id']}',
              `unit` = '{$row['unit_no']}',
              `street` = '{$row['street_address1']}',
              `city` = '{$row['city']}',
              `area` = '{$row['state']}',
              `zip` = '{$row['zip']}',
              `created_at` = '{$row['created_at']}',
              `updated_at` = '{$row['updated_at']}'
            "
          );
        }
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE cj_address
                ADD street address1 LONGTEXT NOT NULL,
                ADD number address2 LONGTEXT NOT NULL,
                DROP area,
                DROP unit,
                CHANGE zip zip VARCHAR(15) NOT NULL,
                CHANGE country country VARCHAR(3) DEFAULT 'USA',
                CHANGE district state VARCHAR(7) DEFAULT NULL"
        );
    }
}
