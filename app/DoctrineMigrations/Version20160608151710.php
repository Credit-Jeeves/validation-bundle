<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160608151710 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $template = <<<TEMPLATE
      {% extends "RjComponentBundle:Mailer:base.html.twig" %}
      {% block h1 %}Welcome to RentTrack!{% endblock %}
      {% block email %}
        <p>
          Ready to get started? Check out the Getting Started Guide. It\'ll take you
          through the basics of online rent payments and the benefits of rent reporting.
        </p>
      {% endblock %}
      {% block button %}
        <br/>
        <div style="text-align:center">
          <a href="http://help.renttrack.com/knowledgebase/articles/907050" style="
              border: none;
              padding: 2px 7px;
              text-align: left;
              color: white;
              font-size: 14px;
              text-shadow: 1px 1px 3px #636363;
              filter: dropshadow(color=#636363, offx=1, offy=1);
              cursor: pointer;
              background-color: #669900;
              -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';
              filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);
              zoom: 1;
              text-decoration: none;
              -moz-border-radius: 4px;
              -webkit-border-radius: 4px;
              border-radius: 4px;
              font-weight: normal
          ">
              Show Me →
          </a>
        </div>
        <br/>
      {% endblock %}
TEMPLATE;
        $this->addSql(
            "UPDATE email_translation
            SET value = '$template'
            WHERE (property = 'body' or property = 'bodyHtml')
            AND translatable_id = (SELECT id FROM email WHERE name = 'welcome.html')"
        );
        $this->addSql(
            "UPDATE email_translation
            SET value = 'Welcome to RentTrack'
            WHERE property = 'subject'
            AND translatable_id = (SELECT id FROM email WHERE name = 'welcome.html')"
        );
    }
    public function down(Schema $schema)
    {
        $template = <<<TEMPLATE
{% extends "CoreBundle:Mailer:base.html.twig" %}{% block h1 %}Welcome to CreditJeeves{% endblock %}{% block email %}<p>You have taken the first step towards your new car.</p><p>To see your customized action plan, sign in at <a href="http://my.creditjeeves.com/">cj</a> anytime.</p><strong>Get started today:</strong><ul>  <li>Understand<a href="http://www.creditjeeves.com/educate/understand-your-credit-score">how your credit score is determined</a></li><li>Review your <a href="http://cj/_dev.php/?">action plan</a> and decide what step you will take first.</li><li>Click on the "learn more" link next to that step to find out what to do.</li></ul><i>Trouble answering the verification questions?</i><p>It is a good idea to get a <a href="https://www.annualcreditreport.com/"> free copy of your credit report </a> to see if contains something you do not recognize. You can also contact <a href="mailto:help@creditjeeves.com">help@creditjeeves.com</a> if your account becomes locked. </p><i>We want to hear from you!</i><p>Please <a href="http://creditjeeves.uservoice.com/">send us your feedback</a> on how we can make the product better for you.</p>{% endblock %}
TEMPLATE;
        $this->addSql(
            "UPDATE email_translation
            SET value = '$template'
            WHERE (property = 'body' or property = 'bodyHtml')
            AND translatable_id = (SELECT id FROM email WHERE name = 'welcome.html')"
        );
        $this->addSql(
            "UPDATE email_translation
            SET value = 'Welcome to Credit Jeeves'
            WHERE property = 'subject'
            AND translatable_id = (SELECT id FROM email WHERE name = 'welcome.html')"
        );
    }
}
