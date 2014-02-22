<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20140221112355 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "INSERT INTO email
                SET name = 'rjPendingOrder.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjPendingOrder.html"),
            locale = "en",
            property = "body",
            value = "
            {% extends \'RjComponentBundle:Mailer:base.html.twig\' %}
            {% block h1 %}Dear {{ landlordFirstName }},{% endblock %}
            {% block email %}
                Hi {{ tenantName }}! <br /><br />

                Your rent payment to {{ groupName }} was sent just now.
                They should see the deposit in their account in 1-3 days.

                The details:

                <table width=\'100%\' style=\'border: 1px solid #C8C8C8; border-collapse: collapse;\'>
                <tbody>
                <tr style=\'border: 1px solid #C8C8C8;\'>
                    <td style=\'padding:5px;\'>{{ \'order.date.time\' | trans }}:</td>
                    <td style=\'padding:5px;\'>{{ orderTime }}</td>
                </tr>
                <tr style=\'border: 1px solid #C8C8C8;\'>
                    <td style=\'padding:5px;\'>{{ \'order.transaction.id\' | trans }}:</td>
                    <td style=\'padding:5px;\'>{{ transactionID }}</td>
                </tr>
                <tr style=\'border: 1px solid #C8C8C8;\'>
                    <td style=\'padding:5px;\'>{{ \'amount\' | trans }}:</td>
                    <td style=\'padding:5px;\'>{{ amount }}</td>
                </tr>
                {% if fee > 0 %}
                <tr style=\'border: 1px solid #C8C8C8;\'>
                    <td style=\'padding:5px;\'>{{ \'order.fee\' | trans }}:</td>
                    <td style=\'padding:5px;\'>{{ fee }}</td>
                </tr>
                {% else %}
                <tr style=\'border: 1px solid #C8C8C8;\'>
                    <td style=\'padding:5px;\'>{{ \'order.fee\' | trans }}:</td>
                    <td style=\'padding:5px;\'>{{ \'order.fee.free\' | trans }}</td>
                </tr>
                {% endif %}
                <tr style=\'border: 1px solid #C8C8C8;\'>
                    <td style=\'padding:5px;\'>{{ \'order.total\' | trans }}:</td>
                    <td style=\'padding:5px;\'>{{ total }}</td>
                </tr>

                </tbody>
                </table>
                </br>
                {{ \'order.receipt.footer\' | trans }}
            {% endblock %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjPendingOrder.html"),
                locale = "en",
                property = "subject",
                value = "Your Rent is Processing"'
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjPendingOrder.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjPendingOrder.html"'
        );

    }
}
{

} 
