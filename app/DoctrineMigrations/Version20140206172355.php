<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20140206172355 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "INSERT INTO email
                SET name = 'rjOrderCancelToLandlord.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjOrderCancelToLandlord.html"),
            locale = "en",
            property = "body",
            value = "
            {% extends \'RjComponentBundle:Mailer:base.html.twig\' %}
            {% block h1 %}Dear {{ landlordFirstName }},{% endblock %}
            {% block email %}
                {% if orderStatus == \'refunded\' %}

                Per your tenant\'s request, their rent of {{ rentAmount }} sent on {{ orderDate }} was refunded
                and will be deducted from your account within a couple of days. Please contact your tenant
                if you have any questions regarding this refund.
                {% elseif orderStatus == \'cancelled\' %}

                Per your your tenant\'s request, their rent payment of {{ rentAmount }} sent on {{ orderDate }}
                was cancelled. You will not see a deposit in your account since it was cancelled before
                payment settlement. Please contact your tenant if you have any questions regarding this cancellation.
                {% else %}

                Your tenant\'s payment of {{ rentAmount }} sent on {{ orderDate }} was returned. This amount
                has been deducted from your account per the RentTrack terms of service. Your rent is currently not paid.
                Please contact your tenant if to arrange another payment.


                RentTrack Customer Support will also reach out to your tenant to see if their payment source information
                can be corrected.
                {% endif %}

                If you have any other questions, please contact help@renttrack.com or call 866-841-9090 x2.
            {% endblock %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjOrderCancelToLandlord.html"),
                locale = "en",
                property = "subject",
                value = "Your Tenant\'s  Rent Payment was Reversed"'
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjOrderCancelToLandlord.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjOrderCancelToLandlord.html"'
        );

    }
}
