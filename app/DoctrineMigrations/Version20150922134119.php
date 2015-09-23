<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150922134119 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "INSERT INTO email
                SET name = 'rjSecondChanceForContract.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjSecondChanceForContract.html"),
            locale = "en",
            property = "body",
            value = "
                <H1>Pay Rent Smarter</H1>
                <H2>Set up your first payment in minutes</H2>
                {{ FNAME }} - we noticed you still haven\'t set up an online rent payment with {{ LANDLORDGR }}.
                Credit follows you for your whole life - it\'s good to start off on the right track.
                RentTrack is the only company that can report your rent payments to major credit bureaus so you can
                build credit history without taking on additional debt.
                <BR><BR>
                Building good credit history means you can put yourself in the best position when you buy a car, get
                a loan, mortgage, apply for insurance, for a job and more*.
                {% if MONTHSLEFT %}
                With {{ MONTHSLEFT }} months left in your lease, you have a great opportunity
                for improvement - most tenants see a score increase within two months.
                {% else %}
                This is a great opportunity for improvement - most tenants see a score increase within two months.
                {% endif %}
                <BR><BR>
                Your property manager is offering you this benefit for only {{ FEEACH }} per e-Check
                or {{ FEECC }} for Credit Card Payments.
                <BR><BR>
                {% if INVITECODE %}
                <a href=\"https://my.renttrack.com/tenant/invite/{{ INVITECODE }}\">Pay Rent Online Today</a>
                {% else %}
                <a href=\"https://my.renttrack.com/\">Pay Rent Online Today</a>
                {% endif %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjSecondChanceForContract.html"),
                locale = "en",
                property = "subject",
                value = "Build Credit When You Pay Rent"'
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql(
            'DELETE email_translation
                FROM email_translation, email
                WHERE email.id = email_translation.translatable_id
                AND email.name = "rjSecondChanceForContract.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjSecondChanceForContract.html"'
        );
    }
}
