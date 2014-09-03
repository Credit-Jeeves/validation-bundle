<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;

class ResidentClient extends AbstractClient
{
    public function loginCheck()
    {
        $parameters = array(
            'ImportCheck_Login' => array_merge(
                $this->getLoginCredentials(),
                array(
                    "CheckDoc" => "xml"
                )
            )
        );

        return $this->processRequest('ImportCheck_Login', $parameters);
    }

    public function getResidentData()
    {
    }
}
