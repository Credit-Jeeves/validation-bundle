<?php

namespace Application\Migrations;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use CreditJeeves\DataBundle\Entity\Dealer;
use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Group;
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
    }

    public function down(Schema $schema)
    {
    }

    public function postUp(Schema $schema)
    {
        $dealerCode = $this->container->getParameter('api.admin_dealer_code');

        if (empty($dealerCode)) {
            throw new \Exception("Error configuration. Don't have api.admin_dealer_code");
        }

        $em = $this->container->get('doctrine.orm.entity_manager');

        $holding = new Holding();
        $holding->setName('700Credit');

        $group = new Group();
        $group->setHolding($holding);
        $group->setName('700Credit');
        $group->setTargetScore(900);
        $group->setType(GroupType::GENERIC);

        $dealer = new Dealer();
        $dealer->setFirstName('700Credit');
        $dealer->setLastName('700Credit');
        $dealer->setEmail('support@700credit.com');
        $dealer->setInviteCode($dealerCode);
        $dealer->setIsActive(true);
        $dealer->setIsSuperAdmin(true);
        $dealer->setIsHoldingAdmin(true);
        $dealer->setPassword(md5('pass'));
        $dealer->setHolding($holding);
        $dealer->addDealerGroup($group);

        $em->persist($holding);
        $em->persist($group);
        $em->persist($dealer);

        $em->flush();
    }

}