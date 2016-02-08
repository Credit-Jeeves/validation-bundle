<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160129155844 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Migrations\AbortMigrationException
     */
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "INSERT INTO email
                SET name = 'rjFreeReportUpdated.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "rjFreeReportUpdated.html"),
            locale = "en",
            property = "body",
            value = "
                {% extends \"RjComponentBundle:Mailer:base.html.twig\" %}
                {% block h1 %}Dear {{ tenantFirstName }}{% endblock %}
                {% block email %}
                    Your latest score and credit profile is now available under the \"Build Credit History\" tab.
                    Log into your <a href=\"{{ dashboardLink }}\">dashboard</a> to view your score. We\'ll update this
                    monthly for you so you can track how rent reporting and other factors might be affecting your score.
                {% endblock %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "rjFreeReportUpdated.html"),
                locale = "en",
                property = "subject",
                value = "ScoreTrack Updated"'
        );

        $this->addSql(
            "ALTER TABLE rj_user_settings
                ADD scoretrack_free_until  DATE DEFAULT NULL"
        );

        $this->addSql(
            "ALTER TABLE cj_settings
                ADD scoretrack_free_until INT DEFAULT NULL"
        );
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Migrations\AbortMigrationException
     */
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
                AND email.name = "rjFreeReportUpdated.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "rjFreeReportUpdated.html"'
        );

        $this->addSql(
            "ALTER TABLE rj_user_settings
                DROP scoretrack_free_until"
        );

        $this->addSql(
            "ALTER TABLE cj_settings
                DROP scoretrack_free_until"
        );
    }
}
