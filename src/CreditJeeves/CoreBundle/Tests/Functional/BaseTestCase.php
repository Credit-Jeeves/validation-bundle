<?php
namespace CreditJeeves\CoreBundle\Tests\Functional;

use Behat\MinkBundle\Test\MinkTestCase;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 *
 * @method \Behat\Mink\Mink getMink()
 */
abstract class BaseTestCase extends MinkTestCase
{
    private $url = null;
    protected $envPath = '/_test.php/';

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    protected function getUrl()
    {
        if (null === $this->url) {
            $this->url = 'http://' . $this->getContainer()->getParameter('url') . '/' . $this->envPath;
        }
        return $this->url;
    }
}
