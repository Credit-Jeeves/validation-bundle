<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Serializer;


interface ExportSerializerInterface
{
    public function serialize($data);

    public function getContentType();
}
