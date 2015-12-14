<?php

namespace RentJeeves\TestBundle\Services\Yardi\Clients;

use DateTime;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient as Main;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Message;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Messages;

class ResidentTransactionsClient extends Main
{
    /**
     * @param string $transactionXml
     * @return Messages
     */
    public function importResidentTransactionsLogin($transactionXml)
    {
        $messages = new Messages();
        $messages->setMessage($message = new Message());
        $message->setMessage('Success cancel');

        return $messages;
    }
}
