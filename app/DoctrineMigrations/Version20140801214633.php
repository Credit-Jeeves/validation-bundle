<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140801214633 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "INSERT INTO email
                SET name = 'rjBatchDepositReportLandlord.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $template = <<<EOT
{% extends "RjComponentBundle:Mailer:base.html.twig" %}
{% block h1 %}Batch Deposit Report{% endblock %}
{% block email %}
Dear {{ landlordFirstName }}, <br />
Your batch deposit report for <b>{{ date | date("m/d/Y") }}</b> for group <b>{{ groupName }}</b>
{% if accountNumber %}(Account #{{ accountNumber }}){% endif %} is below:<br />
<br />
{% if batches %}
{% for batch in batches %}
Batch ID: <b>{{ batch.batchId }}</b><br />
Payment Type: <b>{{ ('order.type.' ~ batch.paymentType) | trans }}</b><br />
<table width="100%" style="border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;">
  <thead>
    <tr>
      <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;" nowrap>{{ 'order.transaction.id.short' | trans }}</th>
      <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'order.status' | trans }}</th>
      <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'order.resident' | trans }}</th>
      <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'payment.property' | trans }}</th>
      <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'payment.date_initiated' | trans }}</th>
      <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'amount' | trans }}</th>
    </tr>
  </thead>
  <tfoot>
    <tr>
       <td colspan="5" style="padding:3px;border: 1px solid #4E4E4E;" align="right"><b>{{ 'order.total' | trans }}:</b></td>
       <td style="padding:3px;border: 1px solid #4E4E4E;"><b>\${{ batch.paymentTotal }}</b></td>
    </tr>
   </tfoot>
  <tbody>
    {% for transaction in batch.transactions %}
    <tr>
      <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ transaction.transactionId }}</td>
      <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ ('order.status.text.' ~ transaction.status) | trans }}</td>
      <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ transaction.resident }}</td>
      <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">
      {{ transaction.property }}{% if not transaction.isSingle %}{{ ' #' ~ transaction.unitName }}{% endif %}
      </td>
      <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ transaction.dateInitiated | date("m/d/Y") }}</td>
      <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">\${{ transaction.amount }}</td>
    </tr>
    {% endfor %}
  </tbody>
</table>
<br />
{% endfor %}
{% endif %}
{% if returns %}
Reversals (Each will be Debited Separately)
<table width="100%" style="border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;">
  <thead>
    <tr>
      <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;" nowrap>{{ 'order.transaction.id.short' | trans }}</th>
      <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'order.status' | trans }}</th>
      <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'payment.status_message' | trans }}</th>
      <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'order.resident' | trans }}</th>
      <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'payment.property' | trans }}</th>
      <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'payment.date_reversal' | trans }}</th>
      <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'amount' | trans }}</th>
    </tr>
  </thead>
  <tbody>
    {% for transaction in returns %}
    <tr>
      <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ transaction.transactionId }}</td>
      <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ ('order.status.text.' ~ transaction.status) | trans }}</td>
      <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ transaction.messages }}</td>
      <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ transaction.resident }}</td>
      <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">
      {{ transaction.property }}{% if not transaction.isSingle %}{{ ' #' ~ transaction.unitName }}{% endif %}
      </td>
      <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ transaction.reversalDate | date("m/d/Y") }}</td>
      <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">-\${{ transaction.amount | abs }}</td>
    </tr>
    {% endfor %}
  </tbody>
</table>
{% endif %}
{% if not (returns and batches)  %}
There are no deposits to report.
{% endif %}
{% endblock %}
EOT;
        $template = str_replace("'", "\'", $template);

        $this->addSql(
            "INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = 'rjBatchDepositReportLandlord.html'),
            locale = 'en',
            property = 'body',
            value = '{$template}'"
        );

        $this->addSql(
            "INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = 'rjBatchDepositReportLandlord.html'),
            locale = 'en',
            property = 'subject',
            value = 'Daily Batch Deposit Report'"
        );

        $this->addSql(
            "INSERT INTO email
                SET name = 'rjBatchDepositReportHolding.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $template = <<<EOT
{% extends "RjComponentBundle:Mailer:base.html.twig" %}
{% block h1 %}Batch Deposit Report{% endblock %}
{% block email %}
Dear {{ landlordFirstName }}, <br />
Your batch deposit report for <b>{{ date | date("m/d/Y") }}</b> is below:
{% for group in groups %}
<br />
<br />For group <b>{{ group.groupName }}</b>{% if group.accountNumber %} (Account #{{ group.accountNumber }}){% endif %}:<br />
<br />
{% if group.batches %}
  {% for batch in group.batches %}
  Batch ID: <b>{{ batch.batchId }}</b><br />
  Payment Type: <b>{{ ('order.type.' ~ batch.paymentType) | trans }}</b><br />
  <table width="100%" style="border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;">
    <thead>
      <tr>
        <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;" nowrap>{{ 'order.transaction.id.short' | trans }}</th>
        <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'order.status' | trans }}</th>
        <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'order.resident' | trans }}</th>
        <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'payment.property' | trans }}</th>
        <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'payment.date_initiated' | trans }}</th>
        <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'amount' | trans }}</th>
      </tr>
    </thead>
    <tfoot>
      <tr>
         <td colspan="5" style="padding:3px;border: 1px solid #4E4E4E;" align="right"><b>{{ 'order.total' | trans }}:</b></td>
         <td style="padding:3px;border: 1px solid #4E4E4E;"><b>\${{ batch.paymentTotal }}</b></td>
      </tr>
     </tfoot>
    <tbody>
      {% for transaction in batch.transactions %}
      <tr>
        <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ transaction.transactionId }}</td>
        <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ ('order.status.text.' ~ transaction.status) | trans }}</td>
        <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ transaction.resident }}</td>
        <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">
          {{ transaction.property }}{% if not transaction.isSingle %}{{ ' #' ~ transaction.unitName }}{% endif %}
        </td>
        <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ transaction.dateInitiated | date("m/d/Y") }}</td>
        <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">\${{ transaction.amount }}</td>
      </tr>
      {% endfor %}
    </tbody>
  </table>
  <br />
  {% endfor %}
{% endif %}
{% if group.returns %}
  Reversals (Each will be Debited Separately)
  <table width="100%" style="border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;">
    <thead>
      <tr>
        <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;" nowrap>{{ 'order.transaction.id.short' | trans }}</th>
        <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'order.status' | trans }}</th>
        <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'payment.status_message' | trans }}</th>
        <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'order.resident' | trans }}</th>
        <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'payment.property' | trans }}</th>
        <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'payment.date_reversal' | trans }}</th>
        <th style="padding:3px;border: 1px solid #4E4E4E;background: #ccc;">{{ 'amount' | trans }}</th>
      </tr>
    </thead>
    <tbody>
      {% for transaction in group.returns %}
      <tr>
        <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ transaction.transactionId }}</td>
        <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ ('order.status.text.' ~ transaction.status) | trans }}</td>
        <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ transaction.messages }}</td>
        <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ transaction.resident }}</td>
        <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">
        {{ transaction.property }}{% if not transaction.isSingle %}{{ ' #' ~ transaction.unitName }}{% endif %}
        </td>
        <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">{{ transaction.reversalDate | date("m/d/Y") }}</td>
        <td style="padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}">-\${{ transaction.amount | abs }}</td>
      </tr>
      {% endfor %}
    </tbody>
  </table>
{% endif %}
{% if not (group.returns and group.batches) %}
There are no deposits to report.
{% endif %}
{% endfor %}
{% endblock %}
EOT;
        $template = str_replace("'", "\'", $template);

        $this->addSql(
            "INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = 'rjBatchDepositReportHolding.html'),
            locale = 'en',
            property = 'body',
            value = '{$template}'"
        );

        $this->addSql(
            "INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = 'rjBatchDepositReportHolding.html'),
            locale = 'en',
            property = 'subject',
            value = 'Daily Batch Deposit Report'"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjBatchDepositReportHolding.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjBatchDepositReportHolding.html"'
        );

        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjBatchDepositReportLandlord.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjBatchDepositReportLandlord.html"'
        );
    }
}
