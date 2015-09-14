<?php
namespace RentJeeves\TestBundle\Tests\Unit;

use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class UnitTestBase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function setUp()
    {
        /*
         * To enable debug logging set PHPUNITDEBUG environment variable before running tests.
         * For example:
         *
         *   export PHPUNITDEBUG=true ; phpunit MyCoolTestCase.php
         */
        $logLevel = (getenv('PHPUNITDEBUG') == '') ? Logger::ERROR : Logger::DEBUG;
        $this->logger = new Logger('UnitTest');
        $this->logger->pushHandler(new StreamHandler('php://stdout', $logLevel));
        $this->logger->debug("DEBUG Logging enabled");
    }
}
