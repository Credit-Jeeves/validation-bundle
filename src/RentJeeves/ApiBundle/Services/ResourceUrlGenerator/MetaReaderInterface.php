<?php

namespace RentJeeves\ApiBundle\Services\ResourceUrlGenerator;

interface MetaReaderInterface
{
    /**
     * @param $object
     * @return mixed
     */
    public function read($object);
}
