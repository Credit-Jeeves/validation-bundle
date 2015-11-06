<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151106115547 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        /************* rjBatchDepositReportHolding **************/
        $templateForHolding = <<<BDRH
      "{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}
      {% block h1 %}Batch Deposit Report{% endblock %}
      {% block email %}
      Dear {{ landlordFirstName }}, <br /><br />
            {% if resend %}
            <br />RESENDING: Due to delayed deposit information from our payment processor.<br /><br />
            {%endif%}
            Your batch deposit report for <b>{{ date | date(\"m/d/Y\") }}</b>* is below.
      <br />
      <br />
      Note: Clients with integrated accounting software must post all Security Deposit and Application Fee payments manually.
      <br />
      {% for group in groups %}
      <br />For group <b>{{ group.groupName }}</b>:<br />
      <br />
      {% if group.batches %}
       {% for batch in group.batches %}
            For: {{ batch.depositAccountType | trans }} (Account #{{ batch.accountNumber }})<br />
            Batch ID: <b class=\"batch-id\">{{ batch.batchId }}</b><br />
      {% if group.groupPaymentProcessor == 'heartland' %}
               Payment Type: <b>{{ ('order.type.' ~ batch.paymentType) | trans }}</b><br />
      {% endif %}
      <table width=\"100%\" style=\"border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;\">
          <thead>
            <tr>
              <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>{{ 'order.transaction.id.short' | trans }}</th>
              <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ 'order.status' | trans }}</th>
              <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ 'order.resident' | trans }}</th>
              <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ 'payment.property' | trans }}</th>
              <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ 'payment.date_initiated' | trans }}</th>
              <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ 'amount' | trans }}</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
               <td colspan=\"5\" style=\"padding:3px;border: 1px solid #4E4E4E;\" align=\"right\"><b>{{ 'order.total' | trans }}:</b></td>
               <td style=\"padding:3px;border: 1px solid #4E4E4E;\"><b>\${{ batch.paymentTotal }}</b></td>
            </tr>
           </tfoot>
          <tbody>
            {% for transaction in batch.transactions %}
            <tr>
              <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}\">{{ transaction.transactionId }}</td>
              <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}\">{{ ('transaction.status.text.' ~ transaction.transactionStatus) | trans }}</td>
              <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}\">{{ transaction.resident }}</td>
              <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}\">
                {{ transaction.property }}{% if not transaction.isSingle %}{{ ' #' ~ transaction.unitName }}{% endif %}
              </td>
              <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}\">{{ transaction.dateInitiated | date(\"m/d/Y\") }}</td>
              <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}\">\${{ transaction.amount }}</td>
            </tr>
            {% endfor %}
          </tbody>
        </table>
        <br />
        {% endfor %}
      {% endif %}
      {% if group.returns %}
        Reversals {% if group.groupPaymentProcessor == \'heartland\' %}(Each will be Debited Separately){% endif %}
        <table width=\"100%\" style=\"border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;\">
          <thead>
            <tr>
              <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>{{ 'order.transaction.id.short' | trans }}</th>
              <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ 'order.status' | trans }}</th>
              <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ 'payment.status_message' | trans }}</th>
              <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ 'order.resident' | trans }}</th>
              <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ 'payment.property' | trans }}</th>
              <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ 'payment.date_reversal' | trans }}</th>
              <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ 'amount' | trans }}</th>
            </tr>
          </thead>
          <tbody>
            {% for transaction in group.returns %}
            <tr>
              <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}\">{{ transaction.transactionId }}</td>
              <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}\">{{ ('order.status.text.' ~ transaction.orderStatus) | trans }}</td>
              <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}\">{{ transaction.messages }}</td>
              <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}\">{{ transaction.resident }}</td>
              <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}\">
              {{ transaction.property }}{% if not transaction.isSingle %}{{ ' #' ~ transaction.unitName }}{% endif %}
              </td>
              <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}\">{{ transaction.reversalDate | date(\"m/d/Y\") }}</td>
              <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle(['background: #eee;', ''], loop.index) }}\">-\${{ transaction.amount | abs }}</td>
            </tr>
            {% endfor %}
          </tbody>
        </table>
      {% endif %}
      {% endfor %}
      <br />* This report is a snapshot-in-time. Occasionally, deposit information is delayed and we may need to resend this report. For the latest deposit information, you can always review the batch deposit report in the Dashboard.
      {% endblock %}"
BDRH;

        $this->addSql(
            "UPDATE email_translation
            SET value = $templateForHolding
            WHERE property = 'body'
            AND translatable_id = (SELECT id FROM email WHERE name = 'rjBatchDepositReportHolding.html')"
        );

        /************* rjBatchDepositReportLandlord **************/

        $templateForLandlord = <<<BDRL
"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}
      {% block h1 %}Batch Deposit Report{% endblock %}
      {% block email %}
      Dear {{ landlordFirstName }}, <br /><br />
            {% if resend %}
            <br />RESENDING: Due to delayed deposit information from our payment processor.<br /><br />
            {%endif%}
            Your batch deposit report for <b>{{ date | date(\"m/d/Y\") }}</b>* for group <b>{{ groupName }}</b> is below.
      <br />
      <br />
      Note: Clients with integrated accounting software must post all Security Deposit and Application Fee payments manually.
      <br />
      <br />
      {% if batches %}
      {% for batch in batches %}
            For: {{ batch.depositAccountType | trans }} (Account #{{ batch.accountNumber }})<br />
            Batch ID: <b class=\"batch-id\">{{ batch.batchId }}</b><br />
      {% if groupPaymentProcessor == \'heartland\' %}
          Payment Type: <b>{{ (\'order.type.\' ~ batch.paymentType) | trans }}</b><br />
      {% endif %}
      <table width=\"100%\" style=\"border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;\">
        <thead>
          <tr>
            <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>{{ \'order.transaction.id.short\' | trans }}</th>
            <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.status\' | trans }}</th>
            <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.resident\' | trans }}</th>
            <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.property\' | trans }}</th>
            <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.date_initiated\' | trans }}</th>
            <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'amount\' | trans }}</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
             <td colspan=\"5\" style=\"padding:3px;border: 1px solid #4E4E4E;\" align=\"right\"><b>{{ \'order.total\' | trans }}:</b></td>
             <td style=\"padding:3px;border: 1px solid #4E4E4E;\"><b>\${{ batch.paymentTotal }}</b></td>
          </tr>
         </tfoot>
        <tbody>
          {% for transaction in batch.transactions %}
          <tr>
            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.transactionId }}</td>
            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ (\'transaction.status.text.\' ~ transaction.transactionStatus) | trans }}</td>
            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.resident }}</td>
            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">
            {{ transaction.property }}{% if not transaction.isSingle %}{{ \' #\' ~ transaction.unitName }}{% endif %}
            </td>
            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.dateInitiated | date(\"m/d/Y\") }}</td>
            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\${{ transaction.amount }}</td>
          </tr>
          {% endfor %}
        </tbody>
      </table>
      <br />
      {% endfor %}
      {% endif %}
      {% if returns %}
      Reversals {% if group.groupPaymentProcessor == \'heartland\' %}(Each will be Debited Separately){% endif %}
      <table width=\"100%\" style=\"border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;\">
        <thead>
          <tr>
            <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>{{ \'order.transaction.id.short\' | trans }}</th>
            <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.status\' | trans }}</th>
            <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.status_message\' | trans }}</th>
            <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.resident\' | trans }}</th>
            <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.property\' | trans }}</th>
            <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.date_reversal\' | trans }}</th>
            <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'amount\' | trans }}</th>
          </tr>
        </thead>
        <tbody>
          {% for transaction in returns %}
          <tr>
            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.transactionId }}</td>
            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ (\'order.status.text.\' ~ transaction.orderStatus) | trans }}</td>
            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.messages }}</td>
            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.resident }}</td>
            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">
            {{ transaction.property }}{% if not transaction.isSingle %}{{ \' #\' ~ transaction.unitName }}{% endif %}
            </td>
            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.reversalDate | date(\"m/d/Y\") }}</td>
            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">-\${{ transaction.amount | abs }}</td>
          </tr>
          {% endfor %}
        </tbody>
      </table>
      {% endif %}
      <br />* This report is a snapshot-in-time. Occasionally, deposit information is delayed and we may need to resend this report. For the latest deposit information, you can always review the batch deposit report in the Dashboard.
      {% endblock %}"
BDRL;

        $this->addSql(
            "UPDATE email_translation
            SET value = $templateForLandlord
            WHERE property = 'body'
            AND translatable_id = (SELECT id FROM email WHERE name = 'rjBatchDepositReportLandlord.html')"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down(Schema $schema)
    {
    }
}
