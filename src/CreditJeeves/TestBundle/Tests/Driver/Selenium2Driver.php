<?php
namespace CreditJeeves\TestBundle\Tests\Driver;

use Behat\Mink\Driver\Selenium2Driver as BaseSelenium2Driver;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use WebDriver\Exception\ElementNotVisible;


/**
 * Selenium2 driver extension
 */
class Selenium2Driver extends BaseSelenium2Driver
{
    const WAIT = 5;

    private $session;

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
                usleep(2000000);
            }
            $i++;
        } while (empty($nodes) && static::WAIT >= $i);

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
            } catch (ElementNotVisible $e) {
                $exception = $e;
                usleep(2000000);
                var_dump("Try N {$i}");
            }
        } while (static::WAIT >= $i);
        throw $exception;
    }
}
