<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20140529172109 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "INSERT INTO email
                SET name = 'rjContractAmountChanged.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjContractAmountChanged.html"),
            locale = "en",
            property = "body",
            value = "
            {% extends \'RjComponentBundle:Mailer:base.html.twig\' %}
            {% block h1 %}Rent Payment Initiated{% endblock %}
            {% block email %}
                Dear {{ tenantName }}! <br />
                <br />
                Your property manager has adjusted the rent amount on your contract to {{ rentAmount }}.
                Since the recurring payment you had set up for {{ paymentAmount }} is no longer correct,
                we have cancelled your recurring payment.<br />
                </br>
                Please <a href="https://my.renttrack.com/">log in to RentTrack</a> and set up a new recurring payment.
                Be sure to specify the correct month that your next recurring payment should count for.<br />
                </br>
                If you have any questions regarding this change, please contact your property manager.
                If you have questions about setting up a new recurring payment,
                please contact us at help@renttrack.com or call (866) 841-9090.</br>
                </br>
                </br>
                </br>
                {{ \'order.receipt.footer\' | trans }}
            {% endblock %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjContractAmountChanged.html"),
                locale = "en",
                property = "subject",
                value = "Your Rent amount was adjusted on your contract"'
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjContractAmountChanged.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjContractAmountChanged.html"'
        );

    }
}
