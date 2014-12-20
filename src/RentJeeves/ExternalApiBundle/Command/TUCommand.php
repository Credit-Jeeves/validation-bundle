<?php

namespace RentJeeves\ExternalApiBundle\Command;

use RentJeeves\DataBundle\Entity\Tenant;
use RentTrack\TransUnionBundle\CCS\Model\TransUnionUser;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TUCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api:trans-union:snapshot')
            ->setDescription('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.default_entity_manager');
        /** @var Tenant $user */
        $user = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('transU@example.com');
        $address = $user->getDefaultAddress();

        $tuUser = new TransUnionUser();
        $tuUser
            ->setClientId($user->getId())
            ->setFirstName($user->getFirstName())
            ->setLastName($user->getLastName())
            ->setDateOfBirth($user->getDateOfBirth()->format('Y-m-d'))
            ->setSsn($user->getSsn())
            ->setStreet($address->getAddress())
            ->setCity($address->getCity())
            ->setState($address->getArea())
            ->setZipCode($address->getZip());

//        $result = $this->getContainer()
//            ->get('transunion.ccs.credit_snapshot')
//            ->getSnapshot(
//                $tuUser,
//                $container->getParameter('transunion.renttrack_snapshot_bundle')
//            );
        $result = $this->getContainer()
            ->get('transunion.ccs.vantage_score_3')
            ->getScore(
                $tuUser,
                $container->getParameter('transunion.renttrack_vantage_score_3_bundle')
            );
        print_r($result);
    }
}
