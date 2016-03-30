<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160330191658 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        //rjTrustedLandlordDenied
        
        $this->addSql(
            "INSERT INTO email
                SET name = 'rjTrustedLandlordDenied.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjTrustedLandlordDenied.html"),
            locale = "en",
            property = "body",
            value = "
            {% extends \"RjComponentBundle:Mailer:base.html.twig\" %}
            {% block h1 %}Hi {{ tenantFirstName }}!{% endblock %}
            {% block email %}
              Unfortunately, we couldn\'t verify your property manager within 72 hours. Your payment has been closed and
              you have not been charged.
              <br />
              Please continue to pay rent how you always have, per your property manager\'s instructions.
              <br />
              If you have additional information that might help us verify your property manager, please email us at
              help@renttrack.com.
              <br />
              Best Regards,
              The RentTrack Team
            {% endblock %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjTrustedLandlordDenied.html"),
                locale = "en",
                property = "subject",
                value = "We were unable to verify your Property Manager"'
        );

        //rjTrustedLandlordApproved

        $this->addSql(
            "INSERT INTO email
                SET name = 'rjTrustedLandlordApproved.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjTrustedLandlordApproved.html"),
            locale = "en",
            property = "body",
            value = "
                {% extends \"RjComponentBundle:Mailer:base.html.twig\" %}
                {% block h1 %}Hi {{ tenantFirstName }}!{% endblock %}
                {% block email %}
                  We\'ve been able to verify your property manager! Checks will go to:
                  {{ trustedLandlordFullName }}
                  <br />
                  {{ trustedLandlordAddress }}
                  <br /><br />
                  Your payment has been re-enabled and you should receive a receipt shortly.
                  If you set up a recurring payment, your recurring day has been adjusted to today.
                  If this is incorrect, please be sure to edit your recurring payment tomorrow.
                  <br />
                  We\'re here to help,
                  The RentTrack Team
                {% endblock %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjTrustedLandlordApproved.html"),
                locale = "en",
                property = "subject",
                value = "Your PM has been Approved"'
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
                AND email.name = "rjTrustedLandlordDenied.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjTrustedLandlordDenied.html"'
        );

        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjTrustedLandlordApproved.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjTrustedLandlordApproved.html"'
        );
    }
}
