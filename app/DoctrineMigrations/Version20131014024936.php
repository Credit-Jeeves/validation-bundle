<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131014024936 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_payment
                CHANGE dueDate due_date INT NOT NULL,
                CHANGE startMonth start_month INT NOT NULL,
                CHANGE startYear start_year INT NOT NULL,
                CHANGE endMonth end_month INT DEFAULT NULL,
                CHANGE endYear end_year INT DEFAULT NULL"
        );
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "ALTER TABLE rj_payment
                CHANGE due_date dueDate INT NOT NULL,
                CHANGE start_month startMonth INT NOT NULL,
                CHANGE start_year startYear INT NOT NULL,
                CHANGE end_month endMonth INT DEFAULT NULL,
                CHANGE end_year endYear INT DEFAULT NULL"
        );
    }
}
