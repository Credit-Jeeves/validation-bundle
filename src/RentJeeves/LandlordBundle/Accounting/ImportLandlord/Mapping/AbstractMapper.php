<?php

namespace RentJeeves\LandlordBundle\Accounting\ImportLandlord\Mapping;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class AbstractMapper implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var array Associative array for mapping
     */
    protected $data;

    /**
     * @var Group
     */
    protected $group;

    /**
     * Set a EntityManager.
     *
     * @param EntityManagerInterface $em
     */
    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function get($key)
    {
        if (false === isset($this->data[$key])) {
            $message = sprintf('[Mapping] : value with key \'%s\' not found', $key);
            $this->logger->error($message);

            throw new \InvalidArgumentException($message);
        }

        return $this->data[$key];
    }

    /**
     * @return Group
     */
    protected function getGroup()
    {
        return $this->group;
    }

    /**
     * @param array $data
     * @param Group $group
     *
     * @return object
     */
    public function map(array $data, Group $group = null)
    {
        $this->data = $data;
        $this->group = $group;

        return $this->mapObject();
    }

    /**
     * Use {@see get} to retrieve data
     *
     * @return object
     */
    abstract protected function mapObject();
}
