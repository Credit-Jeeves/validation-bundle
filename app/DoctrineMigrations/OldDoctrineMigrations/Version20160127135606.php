<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160127135606 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $template = <<<tmplt
        {% extends "RjComponentBundle:Mailer:base.html.twig" %}
        {% block h1 %}Rent Payment Initiated{% endblock %}
        {% block email %}
            Hi {{ tenantName }}! <br /><br />

            Your {{paymentType | replace({"_": "-"})}} rent payment to {{ groupName }} was just initiated. Your card or bank account has been charged.
            <br /><br />
            The details:

            <table width="100%" style="border: 1px solid #C8C8C8; border-collapse: collapse;">
            <tbody>
            <tr style="border: 1px solid #C8C8C8;">
                <td style="padding:5px;">{{ "common.charged_at" | trans }}:</td>
                <td style="padding:5px;">{{ orderTime }}</td>
            </tr>
            <tr style="border: 1px solid #C8C8C8;">
                <td style="padding:5px;">{{ "order.transaction.id" | trans }}:</td>
                <td style="padding:5px;">{{ transactionID }}</td>
            </tr>
            <tr style="border: 1px solid #C8C8C8;">
                <td style="padding:5px;">{{ "payment_source" | trans }}:</td>
                <td style="padding:5px;">{{ lastFour }}</td>
            </tr>
            <tr style="border: 1px solid #C8C8C8;">
              <td style="padding:5px;">{{ "email.rent_amount" | trans }}:</td><td style="padding:5px;">{{ rentAmount }}</td>
            </tr>
            <tr style="border: 1px solid #C8C8C8;">
              <td style="padding:5px;">{{ "email.other_amount" | trans }}:</td><td style="padding:5px;">{{ otherAmount }}</td>
            </tr>
            {% if fee > 0 %}
            <tr style="border: 1px solid #C8C8C8;">
                <td style="padding:5px;">{{ "order.fee" | trans }}:</td>
                <td style="padding:5px;">{{ fee }}</td>
            </tr>
            {% else %}
            <tr style="border: 1px solid #C8C8C8;">
                <td style="padding:5px;">{{ "order.fee" | trans }}:</td>
                <td style="padding:5px;">{{ "order.fee.free" | trans }}</td>
            </tr>
            {% endif %}
            <tr style="border: 1px solid #C8C8C8;">
                <td style="padding:5px;">{{ "order.total" | trans }}:</td>
                <td style="padding:5px;">{{ total }}</td>
            </tr>

            </tbody>
            </table>
            </br>
        {% if type == "bank" %}
        This payment will appear on your bank statement as RENTTRACK.
        {% elseif type == "card" %}
        This payment will appear on your bank statement as {{ statementDescriptor }}.
        {% endif %}
        {% endblock %}
tmplt;
        $this->addSql(
            "UPDATE email_translation
            SET value = '$template'
            WHERE (property = 'body' or property = 'bodyHtml')
            AND translatable_id = (SELECT id FROM email WHERE name = 'rjPendingOrder.html')"
        );

        $template = <<<tmplt
        {% extends "RjComponentBundle:Mailer:base.html.twig" %}
        {% block h1 %}Your Payment is Complete{% endblock %}
        {% block email %}
        {% if nameTenant %}
          Hi {{ nameTenant }}! <br /><br />
        {% else %}
          Hello!  <br /><br />
        {% endif %}

        Your {{paymentType | replace({"_": "-"})}} {{ depositType | trans }} payment to {{ groupName }} is complete*.

        The details:

        <table
          width="100%"
          style="
            border: 1px solid #C8C8C8;
            border-collapse: collapse;
         "
        >
          <tbody>
            <tr style="border: 1px solid #C8C8C8;">
              <td style="padding:5px;">{{ "common.charged_at" | trans }}:</td><td style="padding:5px;">{{ datetime }}</td>
            </tr>
            <tr style="border: 1px solid #C8C8C8;">
              <td style="padding:5px;">{{ "order.transaction.id" | trans }}:</td><td style="padding:5px;">{{ transactionID }}</td>
            </tr>
            <tr style="border: 1px solid #C8C8C8;">
              <td style="padding:5px;">{{ "payment_source" | trans }}:</td><td style="padding:5px;">{{ lastFour }}</td>
            </tr>
            <tr style="border: 1px solid #C8C8C8;">
              <td style="padding:5px;">{{ "email.rent_amount" | trans }}:</td><td style="padding:5px;">{{ rentAmount }}</td>
            </tr>
            <tr style="border: 1px solid #C8C8C8;">
              <td style="padding:5px;">{{ "email.other_amount" | trans }}:</td><td style="padding:5px;">{{ otherAmount }}</td>
            </tr>
            {% if fee > 0 %}
            <tr style="border: 1px solid #C8C8C8;">
              <td style="padding:5px;">{{ "order.fee" | trans }}:</td><td style="padding:5px;">{{ fee }}</td>
            </tr>
            {% else %}
            <tr style="border: 1px solid #C8C8C8;">
              <td style="padding:5px;">{{ "order.fee" | trans }}:</td><td style="padding:5px;">{{ "order.fee.free" | trans }}</td>
            </tr>
            {% endif %}
            <tr style="border: 1px solid #C8C8C8;">
              <td style="padding:5px;">{{ "order.total" | trans }}:</td><td style="padding:5px;">{{ total }}</td>
            </tr>

          </tbody>
        </table>
        If you have any questions, please call 866.841.9090 or email help@renttrack.com.
        <br/>
        <br/>
        {% if type == "bank" %}
        This payment will appear on your bank statement as RENTTRACK.
        {% elseif type == "card" %}
        This payment will appear on your bank statement as {{ statementDescriptor }}.
        {% endif %}
        <br/>
        <p style="font-size:smaller">
        * Credit card payments deposited next business day.
        </p>
        {% endblock %}
tmplt;
        $this->addSql(
            "UPDATE email_translation
            SET value = '$template'
            WHERE (property = 'body' or property = 'bodyHtml')
            AND translatable_id = (SELECT id FROM email WHERE name = 'rjOrderReceipt.html')"
        );
    }

    public function down(Schema $schema)
    {
    }
}
