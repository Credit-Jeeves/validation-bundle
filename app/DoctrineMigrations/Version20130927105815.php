<?php

namespace Application\Migrations;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \DateTime;
use \Exception;
use CreditJeeves\DataBundle\Enum\GroupType;
use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130927105815 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        $dealerCode = $this->container->getParameter('api.admin_dealer_code');

        if (empty($dealerCode)) {
            throw new Exception("Error configuration. Don't have api.admin_dealer_code");
        }

        $date = new Datetime();
        $date = $date->format('Y-m-d H:m:i');
        $sqlHolding = 'INSERT INTO cj_holding (name, created_at, updated_at) VALUES ("700Credit","$date", "$date")';
        $holding = $this->connection->prepare($sqlHolding);
        $holding->execute();
        $holdingId = $this->connection->lastInsertId();

        $sqlUser = 'INSERT INTO cj_user (
                    username,
                    username_canonical,
                    email,
                    email_canonical,
                    enabled,
                    salt,
                    password,
                    last_login,
                    locked,
                    expired,
                    expires_at,
                    confirmation_token,
                    password_requested_at,
                    roles,
                    credentials_expired,
                    credentials_expire_at,
                    first_name,
                    middle_initial,
                    last_name,
                    street_address1,
                    street_address2,
                    unit_no,
                    city,
                    state,
                    zip,
                    phone_type,
                    phone,
                    date_of_birth,
                    ssn,
                    is_active,
                    invite_code,
                    score_changed_notification,
                    offer_notification,
                    culture,
                    has_data,
                    is_verified,
                    has_report,
                    holding_id,
                    is_holding_admin,
                    is_super_admin,
                    created_at,
                    updated_at,
                    type
                    )
                    VALUES (
                    "support@700credit.com",
                    "support@700credit.com",
                    "support@700credit.com",
                    "support@700credit.com",
                    1,
                    "g2rgfwn58nc4kkc8gow4ccgo80ocgck",
                    "7b3e63c45d5cb6859f325ab1447321ef",
                    NULL,
                    "false",
                    "false",
                    NULL,
                    NULL,
                    NULL,
                    "a:0:{}",
                    "false",
                    NULL,
                    "700Credit",
                    NULL,
                    "700Credit",
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    "true",
                    :dealerCode,
                    "true",
                    "true",
                    "en",
                    "true",
                    "none",
                    NULL,
                    :holding,
                    "true",
                    "true",
                    :date1,
                    :date2,
                    "dealer")';
        $user = $this->connection->prepare($sqlUser);
        $user->bindParam('date1', $date);
        $user->bindParam('date2', $date);
        $user->bindParam('holding', $holdingId);
        $user->bindParam('dealerCode', $dealerCode);
        $user->execute();
        $userId = $this->connection->lastInsertId();

        $sqlAccountGroup = "INSERT INTO cj_account_group
                         (cj_affiliate_id,
                          holding_id,
                          parent_id,
                          dealer_id,
                          name,
                          target_score,
                          code,
                          description,
                          website_url,
                          logo_url,
                          phone,
                          fax,
                          street_address_1,
                          street_address_2,
                          city,
                          state,
                          zip,
                          fee_type,
                          contract,
                          contract_date,
                          type,
                          created_at,
                          updated_at
                          )
                          VALUES (
                          NULL,
                          \"$holdingId\",
                          NULL,
                          \"$userId\",
                          \"700Credit\",
                          900,
                          NULL,
                          NULL,
                          NULL,
                          NULL,
                          NULL,
                          NULL,
                          NULL,
                          NULL,
                          NULL,
                          NULL,
                          NULL,
                          \"flat\",
                          NULL,
                          NULL,
                          \"generic\",
                          \"$date\",
                          \"$date\")";
        $accountGroup = $this->connection->prepare($sqlAccountGroup);
        $accountGroup->execute();

        $groupId = $this->connection->lastInsertId();

        $dealerGroup = "INSERT INTO cj_dealer_group (dealer_id, group_id) VALUES ({$userId},{$groupId})";
        $dealerGroup= $this->connection->prepare($dealerGroup);
        $dealerGroup->execute();
    }

    public function down(Schema $schema)
    {
    }
}
