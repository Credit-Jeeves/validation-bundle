<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20141114153016 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "INSERT INTO email
                SET name = 'rjYardiPaymentAcceptedTurnOn.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $template = <<<EOT
      {% extends "RjComponentBundle:Mailer:base.html.twig" %}
      {% block email %}
        Dear {{ TenantName }},
        Your property manager has re-enabled online payments. You can now
        <a href="{{ href }}">log into RentTrack</a> and set up a new payment.
      {% endblock %}
EOT;
        $template = str_replace("'", "\'", $template);

        $this->addSql(
            "INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = 'rjYardiPaymentAcceptedTurnOn.html'),
            locale = 'en',
            property = 'body',
            value = '{$template}'"
        );


        $this->addSql(
            "INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = 'rjYardiPaymentAcceptedTurnOn.html'),
            locale = 'en',
            property = 'subject',
            value = 'Online Payments Enabled'"
        );


        $this->addSql(
            "INSERT INTO email
                SET name = 'rjYardiPaymentAcceptedTurnOff.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $template = <<<EOT
      {% extends "RjComponentBundle:Mailer:base.html.twig" %}
      {% block email %}
        Dear {{ TenantName }},
        Your property manager has disabled online payments. Any unprocessed payments you had scheduled
        through RentTrack have been cancelled. Contact your property manager immediately for more information.
      {% endblock %}
EOT;
        $template = str_replace("'", "\'", $template);

        $this->addSql(
            "INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = 'rjYardiPaymentAcceptedTurnOff.html'),
            locale = 'en',
            property = 'body',
            value = '{$template}'"
        );


        $this->addSql(
            "INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = 'rjYardiPaymentAcceptedTurnOff.html'),
            locale = 'en',
            property = 'subject',
            value = 'Online Payments Disabled'"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjYardiPaymentAcceptedTurnOff.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjYardiPaymentAcceptedTurnOff.html"'
        );

        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjYardiPaymentAcceptedTurnOn.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjYardiPaymentAcceptedTurnOn.html"'
        );
    }
}
