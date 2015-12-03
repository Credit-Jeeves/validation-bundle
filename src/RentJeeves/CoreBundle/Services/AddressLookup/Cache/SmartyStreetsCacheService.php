<?php

namespace RentJeeves\CoreBundle\Services\AddressLookup\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\SmartyStreetsCache;

/**
 * This class is used for SmartyStreetsBundle
 * It saves requests and response in db
 */
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
        if (null !== $cache = $this->em->getRepository('RjDataBundle:SmartyStreetsCache')->find($id)) {
            return false;
        }

        $cache = new SmartyStreetsCache();
        $cache->setId($id);
        $cache->setValue($data);

        $this->em->persist($cache);
        $this->em->flush($cache);

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
        if (null === $cache = $this->em->getRepository('RjDataBundle:SmartyStreetsCache')->find($id)) {
            return false;
        }

        $this->em->remove($cache);
        $this->em->flush($cache);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        if (null === $cache = $this->em->getRepository('RjDataBundle:SmartyStreetsCache')->find($id)) {
            return false;
        }

        return $cache->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getStats()
    {
        throw new \LogicException('This method is not implemented');
    }
}
