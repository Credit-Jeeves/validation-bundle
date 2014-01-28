<?php

namespace Application\Migrations;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Exception;
use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20140128123138 extends AbstractMigration implements ContainerAwareInterface
{

    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $dealerCode = $this->container->getParameter('api.admin_dealer_code');

        if (empty($dealerCode)) {
            throw new Exception("Error configuration. Don't have api.admin_dealer_code");
        }

        $sql = 'SELECT holding_id FROM cj_user WHERE cj_user.invite_code="'.$dealerCode.'" AND cj_user.holding_id IS NOT NULL';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $all = $stmt->fetchAll();
        $holdingId = $all['0']['holding_id'];

        $sql = 'UPDATE  `cj_account_group` SET  `type` = "vehicle" WHERE  `cj_account_group`.`holding_id` ="'.$holdingId.'"';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
    }

    public function down(Schema $schema)
    {
    }
}
