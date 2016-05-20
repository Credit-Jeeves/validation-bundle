<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160215122444 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "INSERT INTO email
                SET name = 'rjScoreTrackOrderError.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjScoreTrackOrderError.html"),
            locale = "en",
            property = "body",
            value = "
              {% extends \"RjComponentBundle:Mailer:base.html.twig\" %}
              {% block h1 %}Hi {{ nameTenant }}!{% endblock %}
              {% block email %}
                {{ \'scoretrack.order.error.title\'|trans }}
                <br /><br />
                {{ \'order.error.message\'|trans }}: {{ error }}
                <br />
                <br />
                <hr />
                Payment Date:&nbsp; {{ date }}<br />
                Payment Amount: {{ amount }}<br />
                {% if number %}
                  Reference Number: {{ number }}<br />
                {% endif %}
                </br>
                </br>
                {{ \'order.contact.us\' | trans }}
              {% endblock %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjScoreTrackOrderError.html"),
                locale = "en",
                property = "subject",
                value = "ScoreTrack Payment Error"'
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
                AND email.name = "rjScoreTrackOrderError.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjScoreTrackOrderError.html"'
        );
    }
}
