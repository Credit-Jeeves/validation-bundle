<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140724110644 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $template = <<<EOT
{% extends "RjComponentBundle:Mailer:base.html.twig" %}
{% block h1 %}Pay Rent. Built Credit.{% endblock %}
{% block email %}
{% if nameTenant %}
    Hi {{ nameTenant }}! <br />  <br />
{% else %}
    Hello!  <br /> <br />
{% endif %}
Your landlord, {{ fullNameLandlord }}, would like you to use RentTrack to pay your rent for
{{ rentAddress }}. RentTrack makes it easy to pay rent through secure electronic check transfers
and credit card payments - you get to choose. You also have the opportunity to build credit history by signing up for
credit bureau payment reporting. Finally, <b>paying by electronic checks is completely free</b>.
<br /> <br />

Ready to get something out of your rent payments?<br /> <br />
<a id="payRentLink"
{% if inviteCode %}
  href="http://{{ serverName }}{{ path('tenant_invite', {'code': inviteCode, 'isImported': isImported }) }}"
{% else %}
  href="http://{{ serverName }}/"
{% endif %}
  style="
                border: none;
                padding: 2px 7px;
                text-align: left;
                color: white;
                font-size: 14px;
                text-shadow: 1px 1px 3px #636363;
                filter: dropshadow(color=#636363, offx=1, offy=1);
                cursor: pointer;
                background-color: #669900;
                -ms-filter: 'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)';
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);
                zoom: 1;
                text-decoration: none;
                -moz-border-radius: 4px;
                -webkit-border-radius: 4px;
                border-radius: 4px;
        ">Pay Rent</a> Still have some questions? <a href="http://www.renttrack.com/how-it-works">Learn More</a>
{% endblock %}

EOT;
        $template = str_replace("'", "\'", $template);
        $sql = "UPDATE email_translation as trans INNER JOIN email as em ON em.id= trans.translatable_id";
        $sql .= " SET trans.value = '{$template}'";
        $sql .= " WHERE em.name = 'rjTenantInvite.html' AND trans.property='body'";

        $this->addSql($sql);
    }

    public function down(Schema $schema)
    {
    }
}
