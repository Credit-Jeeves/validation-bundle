<?php

namespace RentJeeves\CoreBundle\Services;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("soft.deleteable.control")
 */
class SoftDeleteableControl
{
    const NAME = 'softdeleteable';

    private $doctrine;

    /**
     * @InjectParams({
     *     "doctrine"     = @Inject("doctrine")
     * })
     */
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function disable()
    {
        if ($this->isEnable()) {
            $this->doctrine->getManager()->getFilters()->disable(self::NAME);
        }
    }

    public function enable()
    {
        if (!$this->isEnable()) {
            $this->doctrine->getManager()->getFilters()->enable(self::NAME);
        }

    }

    /**
     * Values must contains full path like RentJeeves\DataBundle\Entity\Unit
     *
     * @param array $entities
     */
    public function enableForAllAndDisableForEntities(array $entities)
    {
        $filter = $this->doctrine->getManager()->getFilters()->enable(self::NAME);
        foreach ($entities as $entity) {
            $filter->disableForEntity($entity);
        }
    }

    public function isEnable()
    {
        $filters = $this->doctrine->getManager()->getFilters();

        if (array_key_exists(self::NAME, $filters->getEnabledFilters())) {
            return true;
        }

        return false;
    }
}
