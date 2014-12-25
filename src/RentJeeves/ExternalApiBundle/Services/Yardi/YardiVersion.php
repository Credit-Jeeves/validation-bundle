<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use Doctrine\ORM\EntityManager;
use Exception;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\YardiClientEnum;
use RentJeeves\ExternalApiBundle\Soap\SoapClientFactory;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @DI\Service("yardi.version")
 */
class YardiVersion
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var SoapClientFactory
     */
    protected $clientFactory;

    /**
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;

    protected $logger;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "clientFactory" = @DI\Inject("soap.client.factory"),
     *     "exceptionCatcher" = @DI\Inject("fp_badaboom.exception_catcher")
     * })
     */
    public function __construct(EntityManager $em, SoapClientFactory $clientFactory, ExceptionCatcher $exceptionCatcher)
    {
        $this->em = $em;
        $this->clientFactory = $clientFactory;
        $this->exceptionCatcher = $exceptionCatcher;
    }

    public function run()
    {
        try {
            $holdings = $this->em->getRepository('DataBundle:Holding')->findHoldingsWithYardiSettings(0, 1000);
            foreach ($holdings as $holding) {
                /** @var ResidentTransactionsClient $residentClient */
                $residentClient = $this->clientFactory->getClient(
                    $holding->getYardiSettings(),
                    YardiClientEnum::RESIDENT_TRANSACTIONS
                );
                $version = $residentClient->getVersionNumber();
                $this->logMessage(sprintf("Current version for holding %s is %s", $holding->getName(), $version));
            }
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);
            $this->logMessage($e->getMessage());
        }
    }

    public function usingOutput(OutputInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    protected function logMessage($message)
    {
        if ($this->logger) {
            $this->logger->writeln($message);
        }
    }
}
