<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160510130254 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "INSERT INTO email
                SET name = 'rjOrderProfitStarsComplete.html',
                    createdAt = now(),
                    updatedAt = now()"
        );
        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjOrderProfitStarsComplete.html"),
                locale = "en",
                property = "subject",
                value = "Rent Payment Receipt"'
        );
        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjOrderProfitStarsComplete.html"),
            locale = "en",
            property = "body",
            value = "{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}
                {% block h1 %}Your Rent Check has been Processed{% endblock %}
                {% block email %}
                    Hi {{ firstName }}!
                    <br/>
                    <p>Your rent check payment to {{ groupName }} has been processed.</p>

                    <table width=\"100%\" style=\"border: 1px solid #C8C8C8; border-collapse: collapse;\">
                    <tbody>
                      <tr style=\"border: 1px solid #C8C8C8;\">
                        <td style=\"padding:5px;\">Processed On:</td>
                        <td style=\"padding:5px;\">{{ orderCreatedAt }}</td>
                      </tr>
                      <tr style=\"border: 1px solid #C8C8C8;\">
                        <td style=\"padding:5px;\">Transaction ID:</td>
                        <td style=\"padding:5px;\">{{ transactionId }}</td>
                      </tr>
                      <tr style=\"border: 1px solid #C8C8C8;\">
                        <td style=\"padding:5px;\">Payment Method:</td>
                        <td style=\"padding:5px;\">Paper Check  {{ checkNumber }}</td>
                      </tr>
                      <tr style=\"border: 1px solid #C8C8C8;\">
                        <td style=\"padding:5px;\">Amount:</td>
                        <td style=\"padding:5px;\">${{ orderAmount }}</td>
                      </tr>
                    </tbody>
                    </table>

                    <br/>
                    You can save time in the future by <a href=\"http://{{ serverName }}{{ path(\'tenant_invite\', {\'code\': inviteCode }) }}\">paying online</a>.
                    {% if isPassedACHFee %} It\'s only ${{ achFee }} for e-checks{% else %}It\'s free to pay by e-check{% endif %},
                    and {{ ccFee }}% for credit card payments. Flexibility – there when you need it.

                    <p>Pay your rent with RentTrack and build credit history with each payment.
                    RentTrack is the only company that can report your rent payments to all three major credit bureaus,
                    so you can build credit history without taking on additional debt.</p>

                    <a href=\"http://{{ serverName }}{{ path(\'tenant_invite\', {\'code\': inviteCode }) }}\"
                      style=\"
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
                      \">Sign Up Today</a>

                    <p>We\'re here to help,</p>
                    The RentTrack Team
                {% endblock %}"'
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjOrderProfitStarsComplete.html"'
        );
        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjOrderProfitStarsComplete.html"'
        );
    }
}
