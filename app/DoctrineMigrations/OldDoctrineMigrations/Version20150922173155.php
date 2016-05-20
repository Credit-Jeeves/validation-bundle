<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150922173155 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        /***************** Sending ***************/

        $this->addSql(
            "INSERT INTO email
                SET name = 'rjOrderSending.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjOrderSending.html"),
            locale = "en",
            property = "body",
            value = "
                {% extends \"RjComponentBundle:Mailer:base.html.twig\" %}
                {% block h1 %}Rent Check Sent!{% endblock %}
                {% block email %}
                    Hi {{ firstName }},

                    Your rent check to {{ groupName }} in the amount of {{ checkAmount }} was mailed on {{ sendDate }} and
                    should arrive within 1-3 business days via USPS first-class mail. The check was mailed to {{ mailingAddress }}.
                    If there is anything incorrect in your order, please contact us immediately at help@renttrack.com.
                    Thank you for choosing to pay rent with RentTrack!
                {% endblock %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjOrderSending.html"),
                locale = "en",
                property = "subject",
                value = "Your Rent Check is in the Mail!"'
        );

        /***************** Sending ***************/

        /***************** Refunding ***************/

        $this->addSql(
            "INSERT INTO email
                SET name = 'rjOrderRefunding.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjOrderRefunding.html"),
            locale = "en",
            property = "body",
            value = "
                {% extends \"RjComponentBundle:Mailer:base.html.twig\" %}
                {% block h1 %}Rent Check Stopped!{% endblock %}
                {% block email %}
                    Hi {{ firstName }},

                    Your rent payment of {{ totalAmount }} for your rental at {{ rentalAddress }} has been cancelled
                    and any corresponding check sent in the mail has been stopped.  Your payment should be refunded
                    to your original payment source, \"{{ paymentAcctName }}\", within 1-3 business days.

                    If you did not request a refund or a stop on your rent check, please contact help@renttrack.com
                {% endblock %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjOrderRefunding.html"),
                locale = "en",
                property = "subject",
                value = "Your Rent Payment is being Refunded"'
        );

        /***************** Refunding ***************/

        /***************** Reissued ***************/

        $this->addSql(
            "INSERT INTO email
                SET name = 'rjOrderReissued.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjOrderReissued.html"),
            locale = "en",
            property = "body",
            value = "
                {% extends \"RjComponentBundle:Mailer:base.html.twig\" %}
                {% block h1 %}Rent Check Reissued!{% endblock %}
                {% block email %}
                    Hi {{ firstName }},

                    Your rent check of {{ totalAmount }} for your rental at {{ rentalAddress }} has been reissued.
                    A check is reissued when the mailing address is changed.

                    If you did not request that your rent check be sent to a different mailing address, please contact help@renttrack.com immediately.
                {% endblock %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjOrderReissued.html"),
                locale = "en",
                property = "subject",
                value = "Your Rent Check has been Reissued!"'
        );

        /***************** Reissued ***************/
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjOrderSending.html"'
        );
        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjOrderSending.html"'
        );
        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjOrderRefunding.html"'
        );
        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjOrderRefunding.html"'
        );
        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjOrderReissued.html"'
        );
        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjOrderReissued.html"'
        );
    }
}
