<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160405174110 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "INSERT INTO email
                SET name = 'rjPaymentFlaggedByUntrustedLandlordRule.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $template = <<<tmplt
{% extends "RjComponentBundle:Mailer:base.html.twig" %}
{% block h1 %}More Info Required to Process your Rent Payment.{% endblock %}
{% block email %}
    <p>Hi {{ firstName }}!</p>
    <p>It looks like you\'re the first payor for this property manager.
    We\'ll need some more information before we can process your payment for {{ propertyAddress }}:</p>
    <ul>
        <li>The first page of your Lease</li>
        <li>A photo of your Government-Issued ID</li>
    </ul>
    <p>You can scan these items, or if you have a smart phone, you can snap a picture of each.
    Please send them to help@renttrack.com so we can verify all is above-board.
    We should be able to approve your payment within 72 hours.</p>

    {% if jiraTicket %}<p>Please reference {{ jiraTicket }} when replying to this message.</p>{% endif %}
    <p>Thank you, <br\>
    The RentTrack Team</p>
{% endblock %}
tmplt;
        $this->addSql(
            "INSERT INTO email_translation
               SET translatable_id = (
                 SELECT id FROM email WHERE name = 'rjPaymentFlaggedByUntrustedLandlordRule.html'
               ), locale = 'en', property = 'body', value = '$template'"
        );
        $this->addSql(
            "INSERT INTO email_translation
               SET translatable_id = (
                 SELECT id FROM email WHERE name = 'rjPaymentFlaggedByUntrustedLandlordRule.html'
               ), locale = 'en', property = 'subject', value = 'More Info Required to Process your Rent Payment'"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            "DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = 'rjPaymentFlaggedByUntrustedLandlordRule.html'"
        );
        $this->addSql(
            "DELETE FROM email
                WHERE name = 'rjPaymentFlaggedByUntrustedLandlordRule.html'"
        );
    }
}
