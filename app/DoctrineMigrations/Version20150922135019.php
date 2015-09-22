<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150922135019 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "INSERT INTO email
                SET name = 'rjChurnRecapture.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjChurnRecapture.html"),
            locale = "en",
            property = "body",
            value = "
                {% extends \"RjComponentBundle:Mailer:base.html.twig\" %} {% block email %}
                <H1>Missed a Payment?</H1>
                <H2>We\'ve missed you!</H2>
                    Hi {{ FNAME }}!
                    We noticed you haven\'t made a rent payment in a while.
                    Your last payment was on {{ LAST_PAYMENT_DATE }} for the amount of {{ LAST_PAYMENT_AMOUNT }}.
                {% if LEASE_END %}
                Your lease is currently scheduled to end on {{ LEASE_END }}.
                {% else %}
                Your lease is currently month-to-month and has not ended yet.
                {% endif %}

                {% if REPORTING %}
                We also noticed that you are reporting your rent payments to the bureaus.
                In order to build good credit history, you\'ll need to make consistent rent payments each month.
                 Restarting now will keep you \"current.\"
                {% endif %}
                To set up a new payment, just log back in to RentTrack by visiting
                 <a href=\"https://my.renttrack.com/\">my.renttrack.com</a>.
                {% if SURVEY_URL %}
                If you\'ve decided to no longer use RentTrack - please tell us why by taking this brief survey: {{ SURVEY_URL }}
                We\'re working hard to make the best rent payment service out there, and your feedback will help.
                {% endif %}
                Thank you,
                The RentTrack Team
                {% endblock %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjChurnRecapture.html"),
                locale = "en",
                property = "subject",
                value = "Did you miss a rent payment?"'
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjChurnRecapture.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjChurnRecapture.html"'
        );
    }
}
