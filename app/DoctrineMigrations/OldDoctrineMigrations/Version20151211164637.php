<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151211164637 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            'UPDATE email_translation
            SET value = "{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}
      {% block h1 %}It\'s that time again!{% endblock %}
      {% block email %}
      Your payment to {{ nameHolding }} for {{ address }} is coming up.
      <br /><br />

      {% if paymentType == \'recurring\' %}
        {% if isRecurringPaymentEnded == true %}
          Your recurring payment has ended.  <a href=\"{{ loginUrl }}\">Log in to {{ partnerName }} today</a>
    to set up a one-time or recurring payment.
        {% else %}
          It looks like you have a recurring payment for {{ paymentTotal }} set up, so we\'ll send you another email when we transact your payment.
          Please note that if you are paying by credit card, you will also pay a technology fee with your payment.
          If you need to change your payment details or cancel your payment,
          please <a href=\"{{ loginUrl }}\">log in to {{ partnerName }} today</a> and edit your payment details.
        {% endif %}

      {% elseif paymentType == \'one_time\' %}
        It looks like you already have a payment for {{ paymentTotal }} set up, so we\'ll send you another email when we transact your payment.

      {% else %}
        You do not have any payments set up. <a href=\"{{ loginUrl }}\">Log in to {{ partnerName }} today</a>
    to set up a one-time or recurring payment.
      {% endif %}
      {% endblock %}"
            WHERE (property = "body" or property = "bodyHtml")
            AND translatable_id = (SELECT id FROM email WHERE name = "rjPaymentDue.html")'
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
