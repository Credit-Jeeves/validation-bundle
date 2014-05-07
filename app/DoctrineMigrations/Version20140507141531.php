<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140507141531 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $template = <<<EOT
{% extends 'RjComponentBundle:Mailer:base.html.twig' %}
{% block h1 %}Dear {{ landlordFirstName }},{% endblock %}
{% block email %}
    {% if orderStatus == 'refunded' %}

    At the request of {{ tenantName }}, their rent of {{ rentAmount }} sent on {{ orderDate }} was refunded.
    Any monies already deposited will be deducted from your account within a couple of days.
    Please contact your tenant if you have any questions regarding this refund.
    {% elseif orderStatus == 'cancelled' %}

    At the request of {{ tenantName}}, their rent payment of {{ rentAmount }} sent on {{ orderDate }}
    was cancelled. You will not see a deposit in your account since it was cancelled before
    payment settlement. Please contact your tenant if you have any questions regarding this cancellation.
    {% else %}

    The rent payment by {{ tenantName }} for {{ rentAmount }} sent on {{ orderDate }} was returned.
    Any monies already deposited  will be deducted from your account per the RentTrack terms of service.
    Your tenant's rent is currently unpaid.
    Your tenant may try to pay again through RentTrack, or you may arrange an alternate, immediate payment method.
    {% endif %}

    If you have any other questions, please contact help@renttrack.com or call 866-841-9090 x2.
{% endblock %}

EOT;
        $template = str_replace("'", "\'", $template);
        $sql = "UPDATE email_translation as trans INNER JOIN email as em ON em.id= trans.translatable_id";
        $sql .= " SET trans.value = '{$template}'";
        $sql .= " WHERE em.name = 'rjOrderCancelToLandlord.html' AND trans.property='body'";

        $this->addSql($sql);
    }

    public function down(Schema $schema)
    {
    }
}
