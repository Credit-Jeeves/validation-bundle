<?php

namespace Application\Migrations;

use \DateTime;
use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20131223121550 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql("
                UPDATE `client`
                SET  `allowed_grant_types` =  'a:2:{i:0;s:13:\"refresh_token\";i:1;s:8:\"password\";}'
                WHERE  `client`.`id` =1;
               "
        );

        $date = new Datetime();
        $date = $date->format('Y-m-d H:m:i');
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
                    "support2@700credit.com",
                    "support2@700credit.com",
                    "support2@700credit.com",
                    "support2@700credit.com",
                    1,
                    "6xgi8aje3ocg084k84ws0cg0gk44cs8",
                    "7b3e63c45d5cb6859f325ab1447321ef",
                    NULL,
                    "false",
                    "false",
                    NULL,
                    NULL,
                    NULL,
                    "a:1:{i:0;s:10:\"CREDIT_API\";}",
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
                    NULL,
                    "true",
                    "true",
                    :date1,
                    :date2,
                    "dealer")';
        $user = $this->connection->prepare($sqlUser);
        $user->bindParam('date1', $date);
        $user->bindParam('date2', $date);
        $dealerCode = uniqid();
        $user->bindParam('dealerCode', $dealerCode);
        $user->execute();

        $date = new Datetime();
        $date = $date->format('Y-m-d H:m:i');
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
                    "api@usequity.com",
                    "api@usequity.com",
                    "api@usequity.com",
                    "api@usequity.com",
                    1,
                    "g2rgfwn58nc4kkc8gow4ccgo80ocgck",
                    "848c4abcaa73a1c14c273cf0d394d4a8",
                    NULL,
                    "false",
                    "false",
                    NULL,
                    NULL,
                    NULL,
                    "a:1:{i:0;s:13:\"USE_QUITY_API\";}",
                    "false",
                    NULL,
                    "USEquity",
                    NULL,
                    "USEquity",
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
                    NULL,
                    "true",
                    "true",
                    :date1,
                    :date2,
                    "dealer")';
        $user = $this->connection->prepare($sqlUser);
        $user->bindParam('date1', $date);
        $user->bindParam('date2', $date);
        $dealerCode = uniqid();
        $user->bindParam('dealerCode', $dealerCode);
        $user->execute();
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql("
                UPDATE `client` SET
                `allowed_grant_types` =  'a:5:{i:0;s:5:\"token\";i:1;s:18:\"authorization_code\";i:2;s:8:\"password\";i:3;s:18:\"client_credentials\";i:4;s:13:\"refresh_token\";}'
                WHERE  `client`.`id` =1;
               "
        );


    }
}
