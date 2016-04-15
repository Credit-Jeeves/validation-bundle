<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160328203852 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "INSERT INTO email
                SET name = 'rjOrderPayDirectComplete.html',
                    createdAt = now(),
                    updatedAt = now()"
        );
        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjOrderPayDirectComplete.html"),
                locale = "en",
                property = "subject",
                value = "Your Rent is Paid!"'
        );
        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjOrderPayDirectComplete.html"),
            locale = "en",
            property = "body",
            value = "{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}
                {% block h1 %}Your Rent is Paid!{% endblock %}
                {% block email %}
                    Hi {{ firstName }},
                    We just wanted to let you know that your rent check to {{ groupName }}
                    in the amount of ${{ amount }} was cashed on {{ date }}. Have a good day!
                {% endblock %}"'
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjOrderPayDirectComplete.html"'
        );
        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjOrderPayDirectComplete.html"'
        );
    }
}
