<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150106122554 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "CREATE TABLE rj_group_account_mapping (
                id INT AUTO_INCREMENT NOT NULL,
                group_id BIGINT DEFAULT NULL,
                holding_id BIGINT NOT NULL,
                account_number VARCHAR(255) DEFAULT NULL,
                UNIQUE INDEX UNIQ_3E5EA51BFE54D947 (group_id),
                INDEX IDX_3E5EA51B6CD5FBA3 (holding_id),
                UNIQUE INDEX acc_number_constraint (holding_id, account_number),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE rj_group_account_mapping
                ADD CONSTRAINT FK_3E5EA51BFE54D947
                FOREIGN KEY (group_id)
                REFERENCES cj_account_group (id)"
        );
        $this->addSql(
            "ALTER TABLE rj_group_account_mapping
                ADD CONSTRAINT FK_3E5EA51B6CD5FBA3
                FOREIGN KEY (holding_id)
                REFERENCES cj_holding (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP TABLE rj_group_account_mapping"
        );
    }
}
