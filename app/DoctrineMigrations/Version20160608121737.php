<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160608121737 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $template = <<<TEMPLATE
{% extends "RjComponentBundle:Mailer:base.html.twig" %}
{% block h1 %}Dear {{ landlordFirstName }},{% endblock %}
{% block email %}
  {% if orderStatus == "refunded" %}
      At the request of {{ tenantName }}, their payment of {{ rentAmount }} with transaction ID {{ transactionId }} was refunded on {{ orderDate }}.
      Any monies already deposited will be deducted from your account within a couple of days.
      Please contact your tenant if you have any questions regarding this refund.
  {% elseif orderStatus == "cancelled" %}
      At the request of {{ tenantName}}, their payment of {{ rentAmount }} sent on {{ orderDate }}
      was cancelled. You will not see a deposit in your account since it was cancelled before
      payment settlement. Please contact your tenant if you have any questions regarding this cancellation.
      <br /> <br />
      <b>If payments are posted in real time to your accounting software, you need to cancel/void this payment in your accounting system.</b>
  {% else %}
      The payment by {{ tenantName }} for {{ rentAmount }} with transaction ID {{ transactionId }} was returned on {{ orderDate }} for the following reason: {{ reversalDescription }}.
      <br /><br />
      Any monies already deposited will be deducted from your account per the RentTrack Terms of Service.
      Your tenant\'s payment is currently unpaid.
      <br /><br />
      Your tenant may try to pay again through {{ partnerName }}, or you may arrange an immediate, alternate payment method.
  {% endif %}
  <br /> <br />
  If you have any other questions, please contact help@renttrack.com or call 866-841-9090 x2.
{% endblock %}
TEMPLATE;

        $this->addSql(
            "UPDATE email_translation
            SET value = '$template'
            WHERE (property = 'body' or property = 'bodyHtml')
            AND translatable_id = (SELECT id FROM email WHERE name = 'rjOrderCancelToLandlord.html')"
        );
    }

    public function down(Schema $schema)
    {
        $template = <<<TEMPLATE
{% extends "RjComponentBundle:Mailer:base.html.twig" %}
{% block h1 %}Dear {{ landlordFirstName }},{% endblock %}
{% block email %}
  {% if orderStatus == "refunded" %}

  At the request of {{ tenantName }}, their rent of {{ rentAmount }} sent on {{ orderDate }} was refunded.
  Any monies already deposited will be deducted from your account within a couple of days.
  Please contact your tenant if you have any questions regarding this refund.
  {% elseif orderStatus == "cancelled" %}

  At the request of {{ tenantName}}, their rent payment of {{ rentAmount }} sent on {{ orderDate }}
  was cancelled. You will not see a deposit in your account since it was cancelled before
  payment settlement. Please contact your tenant if you have any questions regarding this cancellation.
  {% else %}

  The rent payment by {{ tenantName }} for {{ rentAmount }} sent on {{ orderDate }} was returned.
  Any monies already deposited  will be deducted from your account per the RentTrack terms of service.
  Your tenant\'s rent is currently unpaid.
  Your tenant may try to pay again through RentTrack, or you may arrange an alternate, immediate payment method.
  {% endif %}

  If you have any other questions, please contact help@renttrack.com or call 866-841-9090 x2.
{% endblock %}
TEMPLATE;

        $this->addSql(
            "UPDATE email_translation
            SET value = '$template'
            WHERE (property = 'body' or property = 'bodyHtml')
            AND translatable_id = (SELECT id FROM email WHERE name = 'rjOrderCancelToLandlord.html')"
        );
    }
}
