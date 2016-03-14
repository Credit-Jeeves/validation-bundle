<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160215130601 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $template = <<<tmplt
{% extends "RjComponentBundle:Mailer:base.html.twig" %}
{% block h1 %}Rent Check Ordered!{% endblock %}
{% block email %}
    Hi {{ firstName }},
    <br /><br />
    Your rent check to {{ groupName }} in the amount of \${{ checkAmount }} was ordered on {{ sendDate }}. It will get mailed soon and
    should arrive by {{ estimatedDelivery }} via first-class mail. The check will be mailed to {{ mailingAddress }}.
    If there is anything incorrect in your order, please contact us immediately at help@renttrack.com.
{% endblock %}
tmplt;
        $this->addSql(
            "UPDATE email_translation
            SET value = '$template'
            WHERE (property = 'body' or property = 'bodyHtml')
            AND translatable_id = (SELECT id FROM email WHERE name = 'rjOrderSending.html')"
        );
        $this->addSql(
            "UPDATE email_translation
            SET value = 'Your Rent Check has been Ordered!'
            WHERE property = 'subject'
            AND translatable_id = (SELECT id FROM email WHERE name = 'rjOrderSending.html')"
        );
    }

    public function down(Schema $schema)
    {
    }
}
