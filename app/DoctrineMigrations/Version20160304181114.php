<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160304181114 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "INSERT INTO email
                SET name = 'rjPostPaymentError.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjPostPaymentError.html"),
            locale = "en",
            property = "body",
            value = "
              {% extends \"RjComponentBundle:Mailer:base.html.twig\" %}
              {% block h1 %}Hi {{ landlordName }}!{% endblock %}
              {% block email %}
                 Here are the details:
                 <br />
                 {% for detail in details %}
                 <ul>
                      <li>Resident ID: {{ detail.residentId }} </li>
                      <li>Resident Name: {{ detail.residentName }}</li>
                      <li>Payment Date: {{ detail.paymentDateFormatted }}</li>
                      <li>Transaction ID: {{ detail.transactionId }}</li>
                      <li>RentTrack Batch Number: {{ detail.rentTrackBatchNumber }}</li>
                      {% if detail.accountingSystemBatchNumber %}
                        <li>Accounting System Batch Number: {{ detail.accountingSystemBatchNumber }}</li>
                      {% endif %}
                 </ul>
                 <br />
                 {% endfor %}

                 <br />
                 You will need to: <br />
                 <ul>
                    <li>* Enter the payment and post the batch manually using the attached CSV which contains
                        all the payments in the batch.
                    </li>
                    <li>* If the resident ID was incorrect in RentTrack, please update this by editing the
                        tenant in RentTrack.
                    </li>
                    <li>* If payments should be blocked, please view the tenant in RentTrack and make sure
                        we are blocking payments - either using your accounting system status, or by blocking them
                        within RentTrack.
                    </li>
                 </ul>
                 {% endblock %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjPostPaymentError.html"),
                locale = "en",
                property = "subject",
                value = "Unable to Post Payment to Accounting System"'
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
                AND email.name = "rjPostPaymentError.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjPostPaymentError.html"'
        );
    }
}
