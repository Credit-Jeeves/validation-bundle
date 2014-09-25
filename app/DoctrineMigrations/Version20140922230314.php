<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140922230314 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "CREATE TABLE order_external_api (id BIGINT AUTO_INCREMENT NOT NULL,
                order_id BIGINT NOT NULL,
                api_type ENUM('yardi')
                    COMMENT '(DC2Type:ExternalApi)' DEFAULT 'yardi' NOT NULL,
                deposit_date DATE NOT NULL,
                INDEX IDX_9EFE2AD8D9F6D38 (order_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE order_external_api
                ADD CONSTRAINT FK_9EFE2AD8D9F6D38
                FOREIGN KEY (order_id)
                REFERENCES cj_order (id)"
        );

        $this->addSql(
            "INSERT INTO email
                SET name = 'rjPushBatchReceiptsReport.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $template = <<<EOT
{% extends "RjComponentBundle:Mailer:base.html.twig" %}
{% block email %}
 The following batch deposits for (deposit-date) were uploaded to your accounting system.
 Please review and post the batches.
 <br />
 <br />
 <table width="100%" style="border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;">
   <thead>
     <tr>
       <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;" nowrap>
          {{ 'common.batch_id'| trans }}
       </th>
       <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">
          {{ 'common.type_ach_cc'| trans }}
       </th>
       <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">
          {{ 'common.total'| trans }}
       </th>
       <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">
          {{ 'common.status'| trans }}
       </th>
     </tr>
   </thead>
   <tbody>
        {% for value in data %}
          <tr>
            <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">
              {{ value['bratchId'] }}
            </td>
            <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">
              {{ value['type'] }}
            </td>
            <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">
              {{ value['total'] }}
            </td>
            <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">
              {{ value['status'] }}
            </td>
          </tr>
        {% endfor %}
      </tbody>
    </table>
{% endblock %}
EOT;
        $template = str_replace("'", "\'", $template);

        $this->addSql(
            "INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = 'rjPushBatchReceiptsReport.html'),
            locale = 'en',
            property = 'body',
            value = '{$template}'"
        );

    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "DROP TABLE order_external_api"
        );

        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjPushBatchReceiptsReport.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjPushBatchReceiptsReport.html"'
        );
    }
}
