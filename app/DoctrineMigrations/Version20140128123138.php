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
        $flag = true;
        while($flag) {
            $sql = 'SELECT * FROM cj_user GROUP BY invite_code HAVING count(id) > 1';
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $sql = array();
            $dontTouch = array(
                'support@700credit.com'
            );
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if (in_array($row['email'], $dontTouch)) {
                    continue;
                }
                $code = uniqid();
                $sql[] = 'UPDATE  `cj_user` SET  `invite_code` = "'.$code.'" WHERE  `id` ="'.$row['id'].'"';
            }

            if (empty($sql)) {
                $flag = false;
                break;
            }

            foreach($sql as $query) {
                $stmt = $this->connection->prepare($query);
                $stmt->execute();
            }
        }
        $this->addSql(
            "CREATE UNIQUE INDEX UNIQ_98C9F4756F21F112 ON cj_user (invite_code)"
        );

        $sql = 'SELECT holding_id FROM cj_user WHERE cj_user.invite_code="'.$dealerCode.'" AND cj_user.holding_id IS NOT NULL';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $all = $stmt->fetchAll();
        if (!isset($all['0']['holding_id'])) {
            throw new Exception("Error configuration. Don't have any user with invite_code={$dealerCode}");
        }
        $holdingId = $all['0']['holding_id'];

        $sql = 'UPDATE  `cj_account_group` SET  `type` = "vehicle" WHERE  `cj_account_group`.`holding_id` ="'.$holdingId.'"';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "DROP INDEX UNIQ_98C9F4756F21F112 ON cj_user"
        );
    }
}
