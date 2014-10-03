<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140925145950 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            'UPDATE  `rj_unit` unit JOIN  `rj_property` property
             ON unit.property_id = property.id
             SET unit.name =  "SINGLE_PROPERTY"
             WHERE property.is_single =  "1"'
        );

    }

    public function down(Schema $schema)
    {
    }
}
