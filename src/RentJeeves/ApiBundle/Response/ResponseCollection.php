<?php

namespace RentJeeves\ApiBundle\Response;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Process\Exception\RuntimeException;

class ResponseCollection extends ArrayCollection
{
    /** @var ResponseFactory */
    public static $factory;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $elements = [])
    {
        parent::__construct($elements);

        if (! self::$factory instanceof ResponseFactory) {
            throw new RuntimeException("Need to set ResponseFactory for correct work");
        }

        $this->prepareElements();
    }

    protected function prepareElements()
    {
        foreach ($this->toArray() as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value)
    {
        if (! $value instanceof ResponseResource) {
            $value = clone self::$factory->getResponse($value);
        }

        parent::set($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function add($value)
    {
        if (! $value instanceof ResponseResource) {
            $value = clone self::$factory->getResponse($value);
        }

        return parent::add($value);
    }
}
