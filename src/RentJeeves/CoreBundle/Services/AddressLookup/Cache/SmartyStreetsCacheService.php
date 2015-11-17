<?php

namespace RentJeeves\CoreBundle\Services\AddressLookup\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\SmartyStreetsCache;

class SmartyStreetsCacheService implements Cache
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        if (null !== $SSCache = $this->em->getRepository('RjDataBundle:SmartyStreetsCache')->find($id)) {
            return false;
        }

        $SSCache = new SmartyStreetsCache();
        $SSCache->setId($id);
        $SSCache->setValue($data);

        $this->em->persist($SSCache);
        $this->em->flush($SSCache);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id)
    {
        return (boolean) $this->em->getRepository('RjDataBundle:SmartyStreetsCache')->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        if (null === $SSCache = $this->em->getRepository('RjDataBundle:SmartyStreetsCache')->find($id)) {
            return false;
        }

        $this->em->remove($SSCache);
        $this->em->flush($SSCache);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        if (null === $SSCache = $this->em->getRepository('RjDataBundle:SmartyStreetsCache')->find($id)) {
            return false;
        }

        return $SSCache->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getStats()
    {
        throw new \LogicException('This method is not implemented');
    }
}
