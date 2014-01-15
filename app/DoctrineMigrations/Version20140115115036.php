<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20140115115036 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "INSERT INTO email
                SET name = 'rjOrderCancel.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjOrderCancel.html"),
            locale = "en",
            property = "body",
            value = "
            {% extends \'RjComponentBundle:Mailer:base.html.twig\' %}
            {% block h1 %}Dear {{ tenantFullName }},{% endblock %}
            {% block email %}
                {% if orderStatus == \'refunded\' %}

                Per your request, your rent of {{ rentAmount }} sent on {{ orderDate }} was refunded and should appear
                in your account within a few days.
                {% elseif orderStatus == \'cancelled\' %}

                Your payment of {{ rentAmount }} sent on {{ orderDate }} was cancelled.
                {% else %}

                Your payment of {{ rentAmount }} sent on {{ orderDate }} was returned. Your rent is currently not paid.

                You will receive a follow up from RentTrack customer support with the reason for return and ways
                to fix it.
                {% endif %}

                If you have any other questions, please contact help@renttrack.com or call 866-841-9090 x2.
            {% endblock %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjOrderCancel.html"),
                locale = "en",
                property = "subject",
                value = "RentTrack.com - Your order status has been changed"'
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjOrderCancel.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjOrderCancel.html"'
        );

    }
}
