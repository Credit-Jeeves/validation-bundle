<?php

namespace RentJeeves\CoreBundle\Command;

use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigratePropertyToPropertyAddressCommand extends BaseCommand
{
    const LIMIT = 900000;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('property:migrate-to-property-address')
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'ID of job')
            ->addOption('page', null, InputOption::VALUE_OPTIONAL, 'Number of page')
            ->setDescription('Migrate Property`s address fields to PropertyAddress.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $page = $input->getOption('page');
        if ($page === null) {
            $this->createJobs();
        } else {
            $this->migratePage($page);
        }
    }

    protected function createJobs()
    {
        $pageAmount = ceil($this->countProperty() / self::LIMIT);
        for ($i = 1; $i <= $pageAmount; $i++) {
            $newJob = new Job($this->getName(), ['--page=' . $i]);
            $this->getEntityManager()->persist($newJob);
        }
        $this->getEntityManager()->flush();

        $this->getLogger()->debug(
            sprintf('Created %d jobs for migrate Property`s address fields to PropertyAddress.', $pageAmount)
        );
    }

    /**
     * @param int $page
     */
    protected function migratePage($page)
    {
        foreach ($this->getPropertiesByPage($page) as $property) {
            $propertyAddress = $this->getPropertyAddressRepository()->findOneBy(['index' => $property->getIndex()]);
            if (null === $propertyAddress) {
                $this->createPropertyAddress($property);
            } else {
                $property->setPropertyAddress($propertyAddress);
            }

            $this->getEntityManager()->flush();
            $this->getLogger()->debug(sprintf('Successfully migrated Property#%d', $property->getId()));
        }
    }

    /**
     * @param Property $property
     */
    protected function createPropertyAddress(Property $property)
    {
        $newPropertyAddress = new PropertyAddress();
        $newPropertyAddress->setNumber($property->getNumber());
        $newPropertyAddress->setStreet($property->getStreet());
        $newPropertyAddress->setCity($property->getCity());
        $newPropertyAddress->setState($property->getArea());
        $newPropertyAddress->setZip($property->getZip());
        $newPropertyAddress->setIsSingle($property->isSingleFromProperty());
        $newPropertyAddress->setGoogleReference($property->getGoogleReference());
        $newPropertyAddress->setKb($property->getKb());
        $newPropertyAddress->setJb($property->getJb());
        $newPropertyAddress->setIndex($property->getIndex());
        $newPropertyAddress->setLat($property->getLat());
        $newPropertyAddress->setLong($property->getLong());

        $property->setPropertyAddress($newPropertyAddress);

        $this->getEntityManager()->persist($newPropertyAddress);
    }

    /**
     * @return int
     */
    protected function countProperty()
    {
        return $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from('RjDataBundle:Property', 'p')
            ->where('p.index IS NOT NULL')
            ->andWhere('p.propertyAddress IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int $page
     *
     * @return Property[]
     */
    protected function getPropertiesByPage($page)
    {
        $offset = ($page - 1) * self::LIMIT;

        return $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('p')
            ->from('RjDataBundle:Property', 'p')
            ->where('p.index IS NOT NULL')
            ->andWhere('p.propertyAddress IS NULL')
            ->setFirstResult($offset)
            ->setMaxResults(self::LIMIT)
            ->orderBy('p.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getPropertyAddressRepository()
    {
        return $this->getEntityManager()->getRepository('RjDataBundle:PropertyAddress');
    }
}
