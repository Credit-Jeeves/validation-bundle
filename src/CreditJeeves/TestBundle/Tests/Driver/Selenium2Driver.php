<?php
namespace CreditJeeves\TestBundle\Tests\Driver;

use Behat\Mink\Driver\Selenium2Driver as BaseSelenium2Driver;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use Exception;

/**
 * Selenium2 driver extension
 */
class Selenium2Driver extends BaseSelenium2Driver
{
    const WAIT_TIMES = 4;
    const TIMEOUT = 500000; // 1000000 = 1 second

    private $session;

    public function start()
    {
        parent::start();
        $this->getWebDriverSession()->window('current')->maximize();
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::setSession()
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
        parent::setSession($session);
    }

    /**
     * Finds elements with specified XPath query.
     *
     * @param   string $xpath
     *
     * @return  array - array of Behat\Mink\Element\NodeElement
     */
    public function find($xpath)
    {
        $nodes = array();
        $i = 0;
        do {
            $nodes = $this->getWebDriverSession()->elements('xpath', $xpath);
            if ($i) {
                usleep(static::TIMEOUT);
//                var_dump("Find try N {$i}");
            }
            $i++;
        } while (empty($nodes) && static::WAIT_TIMES >= $i);

        $elements = array();
        foreach ($nodes as $i => $node) {
            $elements[] = new NodeElement(sprintf('(%s)[%d]', $xpath, $i + 1), $this->session);
        }

        return $elements;
    }

    /**
     * @inheritdoc
     */
    public function click($xpath)
    {
        $i = 0;
        $exception = null;
        do {
            try {
                $i++;
                parent::click($xpath);
                return;
            } catch (Exception $e) {
                $exception = $e;
                usleep(static::TIMEOUT);
//                var_dump("Click try N {$i}");
            }
        } while (static::WAIT_TIMES >= $i);
        throw $exception;
    }

    /**
     * @inheritdoc
     */
    public function setValue($xpath, $value)
    {
        $i = 0;
        $exception = null;
        do {
            try {
                $i++;
                parent::setValue($xpath, $value);
                return;
            } catch (Exception $e) {
                $exception = $e;
                usleep(static::TIMEOUT);
//                var_dump("Set value try N {$i}");
            }
        } while (static::WAIT_TIMES >= $i);
        throw $exception;
    }
}
