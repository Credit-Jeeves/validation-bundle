<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20141006153937 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            'UPDATE rj_contract c
             INNER JOIN cj_user u ON u.id = c.tenant_id
             INNER JOIN cj_account_group g ON c.group_id = g.id
             LEFT JOIN rj_group_settings s ON g.id = s.group_id
             SET c.report_to_trans_union = 1, c.trans_union_start_at = NOW()
             WHERE (s.is_reporting_off = 0 OR s.is_reporting_off IS NULL) AND
             STR_TO_DATE(u.created_at, "%Y-%m-%d") >= STR_TO_DATE("2014-09-09", "%Y-%m-%d")
             AND (c.report_to_trans_union = 0 OR c.report_to_trans_union IS NULL) AND
             c.trans_union_start_at IS NULL
          '
        );

    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
    }
}
