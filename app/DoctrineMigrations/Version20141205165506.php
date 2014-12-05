<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141205165506 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjPushBatchReceiptsReport.html"'
        );

        $template = <<<EOT
{% extends "RjComponentBundle:Mailer:base.html.twig" %}
{% block email %}
 The following batch deposits for {{ data['deposit_date']|date('Y-m-d') }} were uploaded to your accounting system.
 Please review and post the batches.
 <br />
 <br />
 <table width="100%" style="border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;">
   <thead>
     <tr>
       <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;" nowrap>
          {{ 'common.batch_id'| trans }}
       </th>
       <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;" nowrap>
          {{ 'yardi.accounting_batch_id'| trans }}
       </th>
       <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">
          {{ 'common.type_ach_cc'| trans }}
       </th>
       <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">
          {{ 'yardi.email.number_of_payments'| trans }}
       </th>
       <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">
          {{ 'common.status'| trans }}
       </th>
     </tr>
   </thead>
   <tbody>
        {% for value in data['data'] %}
          <tr>
            <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">
              {{ value['payment_batch_id'] }}
            </td>
            <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">
              {{ value['batchId'] }}
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

        $this->addSql(
            "INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = 'rjPushBatchReceiptsReport.html'),
            locale = 'en',
            property = 'subject',
            value = 'Push Batch Receipts Report'"
        );
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
    }
}
