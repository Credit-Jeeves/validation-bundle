<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20141029173717 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE partner_user (id INT AUTO_INCREMENT NOT NULL,
                partner_id BIGINT DEFAULT NULL,
                user_id BIGINT DEFAULT NULL,
                INDEX IDX_DDA7E5519393F8FE (partner_id),
                UNIQUE INDEX UNIQ_DDA7E551A76ED395 (user_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE partner_user
                ADD CONSTRAINT FK_DDA7E5519393F8FE
                FOREIGN KEY (partner_id)
                REFERENCES partner (id)"
        );
        $this->addSql(
            "ALTER TABLE partner_user
                ADD CONSTRAINT FK_DDA7E551A76ED395
                FOREIGN KEY (user_id)
                REFERENCES cj_user (id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "DROP TABLE partner_user"
        );
    }
}
