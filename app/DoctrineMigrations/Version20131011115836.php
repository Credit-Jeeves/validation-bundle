<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use  CreditJeeves\DataBundle\Entity\Client;
use \PDO;
use \Exception;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131011115836 extends AbstractMigration implements ContainerAwareInterface
{

    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "CREATE TABLE auth_code (id INT AUTO_INCREMENT NOT NULL,
                client_id INT NOT NULL,
                user_id BIGINT DEFAULT NULL,
                token VARCHAR(255) NOT NULL,
                redirect_uri LONGTEXT NOT NULL,
                expires_at INT DEFAULT NULL,
                scope VARCHAR(255) DEFAULT NULL,
                UNIQUE INDEX UNIQ_5933D02C5F37A13B (token),
                INDEX IDX_5933D02C19EB6921 (client_id),
                INDEX IDX_5933D02CA76ED395 (user_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE access_token (id INT AUTO_INCREMENT NOT NULL,
                client_id INT NOT NULL,
                user_id BIGINT DEFAULT NULL,
                token VARCHAR(255) NOT NULL,
                expires_at INT DEFAULT NULL,
                scope VARCHAR(255) DEFAULT NULL,
                UNIQUE INDEX UNIQ_B6A2DD685F37A13B (token),
                INDEX IDX_B6A2DD6819EB6921 (client_id),
                INDEX IDX_B6A2DD68A76ED395 (user_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL,
                random_id VARCHAR(255) NOT NULL,
                redirect_uris LONGTEXT NOT NULL
                    COMMENT '(DC2Type:array)',
                secret VARCHAR(255) NOT NULL,
                allowed_grant_types LONGTEXT NOT NULL
                    COMMENT '(DC2Type:array)',
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "CREATE TABLE refresh_token (id INT AUTO_INCREMENT NOT NULL,
                client_id INT NOT NULL,
                user_id BIGINT DEFAULT NULL,
                token VARCHAR(255) NOT NULL,
                expires_at INT DEFAULT NULL,
                scope VARCHAR(255) DEFAULT NULL,
                UNIQUE INDEX UNIQ_C74F21955F37A13B (token),
                INDEX IDX_C74F219519EB6921 (client_id),
                INDEX IDX_C74F2195A76ED395 (user_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE auth_code
                ADD CONSTRAINT FK_5933D02C19EB6921
                FOREIGN KEY (client_id)
                REFERENCES client (id)"
        );
        $this->addSql(
            "ALTER TABLE auth_code
                ADD CONSTRAINT FK_5933D02CA76ED395
                FOREIGN KEY (user_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE access_token
                ADD CONSTRAINT FK_B6A2DD6819EB6921
                FOREIGN KEY (client_id)
                REFERENCES client (id)"
        );
        $this->addSql(
            "ALTER TABLE access_token
                ADD CONSTRAINT FK_B6A2DD68A76ED395
                FOREIGN KEY (user_id)
                REFERENCES cj_user (id)"
        );
        $this->addSql(
            "ALTER TABLE refresh_token
                ADD CONSTRAINT FK_C74F219519EB6921
                FOREIGN KEY (client_id)
                REFERENCES client (id)"
        );
        $this->addSql(
            "ALTER TABLE refresh_token
                ADD CONSTRAINT FK_C74F2195A76ED395
                FOREIGN KEY (user_id)
                REFERENCES cj_user (id)"
        );

        $this->addSql(
            "CREATE TABLE api_update_user (id BIGINT AUTO_INCREMENT NOT NULL,
                user_id BIGINT DEFAULT NULL,
                UNIQUE INDEX UNIQ_33880B58A76ED395 (user_id),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
        $this->addSql(
            "ALTER TABLE api_update_user
                ADD CONSTRAINT FK_33880B58A76ED395
                FOREIGN KEY (user_id)
                REFERENCES cj_user (id)"
        );

        $this->addSql(
            "INSERT INTO `client`
            (`id`,
             `random_id`,
             `redirect_uris`,
             `secret`,
             `allowed_grant_types`
             ) VALUES
            (1,
             'qvxzb7ge734ko4ogwcskwksogoc0wskws40gg8oocokwg404s',
             'a:0:{}',
             '39uyn651qlk4ssws40sgs44cwsskgccoc0o04ccgsccgooowwo',
             'a:5:{i:0;s:5:\"token\";i:1;s:18:\"authorization_code\";i:2;s:8:\"password\";i:3;s:18:\"client_credentials\";i:4;s:13:\"refresh_token\";}'
            )"
        );
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
        
        $this->addSql(
            "ALTER TABLE auth_code
                DROP
                FOREIGN KEY FK_5933D02C19EB6921"
        );
        $this->addSql(
            "ALTER TABLE access_token
                DROP
                FOREIGN KEY FK_B6A2DD6819EB6921"
        );
        $this->addSql(
            "ALTER TABLE refresh_token
                DROP
                FOREIGN KEY FK_C74F219519EB6921"
        );
        $this->addSql(
            "DROP TABLE auth_code"
        );
        $this->addSql(
            "DROP TABLE access_token"
        );
        $this->addSql(
            "DROP TABLE client"
        );
        $this->addSql(
            "DROP TABLE refresh_token"
        );

        $this->addSql(
            "DROP TABLE api_update_user"
        );
    }
}
