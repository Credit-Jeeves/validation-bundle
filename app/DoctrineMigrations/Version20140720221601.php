<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20140720221601 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "INSERT INTO email
                SET name = 'rjReceipt.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjReceipt.html"),
            locale = "en",
            property = "body",
            value = "
                {% extends \'RjComponentBundle:Mailer:base.html.twig\' %}
                {% block email %}
                Dear {{ tenantName }}! <br />
                <br />
                Thank you for purchasing your credit report through Credit Jeeves.
                Your payment was processed successfully and will appear on your next statement.
                Here is your receipt:<br />
                &nbsp;<br />
                <hr />
                Payment Date & Time:&nbsp;{{ date }}<br />
                Payment Amount: {{ amout }}<br />
                Reference Number: {{ number }}<br />
                <br />
                </br>
                </br>
                {{ \'order.receipt.footer\' | trans }}
                {% endblock %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjReceipt.html"),
                locale = "en",
                property = "subject",
                value = "Receipt from Rent Track"'
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjReceipt.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjReceipt.html"'
        );

    }
}
