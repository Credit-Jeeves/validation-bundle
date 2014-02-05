<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20140203143203 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        $this->addSql(
            "INSERT INTO email
                SET name = 'target.html',
                    createdAt = now(),
                    updatedAt = now()"
        );

        $this->addSql(
            'INSERT INTO email_translation
            SET translatable_id = (SELECT id FROM email WHERE name = "target.html"),
            locale = "en",
            property = "body",
            value = "
             {% extends \"CoreBundle:Mailer:base.html.twig\" %}
             {% block h1 %}Congratulations!{% endblock %}
             {% block email %}
               <div mc:edit=\"std_content00\">
                   You have reached your dealer\'s target score of <strong>{{ targetScore }}</strong>
               </div>
               <div mc:edit=\"latest_score_button\">
                   <br />
                   <hr />
                   Log into Credit Jeeves to find out what to do next. Your new car awaits!
                   <br />
                   <a class=\"button\" href=\"{{ loginLink }}\" id=\"viewLatestScoreButton\">View Latest Score</a>
                   <br />
                   <hr />
               </div>
             {% endblock %}"'
        );

        $this->addSql(
            'INSERT INTO email_translation
                SET translatable_id = (SELECT id FROM email WHERE name = "target.html"),
                locale = "en",
                property = "subject",
                value = "Your New Car Awaits - Log into Credit Jeeves"'
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
                AND email.name = "target.html"'
        );

        $this->addSql(
            'DELETE FROM email
                WHERE name = "target.html"'
        );
    }
}
