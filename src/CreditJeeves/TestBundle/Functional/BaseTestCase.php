<?php
namespace CreditJeeves\TestBundle\Functional;

use Behat\Mink\Driver\Selenium2Driver;
use CreditJeeves\TestBundle\BaseTestCase as Base;
use Ton\EmailBundle\EmailReader;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
abstract class BaseTestCase extends Base
{
    private $url = null;

    /**
     * @var \Behat\Mink\Session
     */
    protected $session = null;

    /**
     * @var \Behat\Mink\Element\DocumentElement
     */
    protected $page = null;

    /**
     * @var string
     */
    protected $envPath = '/_test.php/';
    protected $timeout = 15000;

    protected function getUrl()
    {
        if (null === $this->url) {
            $this->url = 'http://' . static::getContainer()->getParameter('server_name') . $this->envPath;
        }

        return $this->url;
    }

    /**
     * @return EmailReader
     */
    protected function getEmailReader()
    {
        return $this->getContainer()->get('ton.email_bundle.email_reader');
    }

    /**
     * @return array
     */
    protected function getEmails()
    {
        return $this->getEmailReader()->getAllEmails();
    }

    protected function clearEmail()
    {
        $this->getEmailReader()->clear();
    }

    /**
     * @inheritdoc
     */
    protected function load($reload = false)
    {
        parent::load($reload);
        $this->clearEmail();
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->session = $this->getMink()->getSession();
        $this->initTestCoverage();
        $this->page = $this->session->getPage();
    }

    /**
     * Local implementation of $this->getMink()->setDefaultSessionName($name);
     *
     * @param string $name
     */
    protected function setDefaultSession($name)
    {
        $this->getMink()->setDefaultSessionName($name);
        $this->session = $this->getMink()->getSession();
        $this->page = $this->session->getPage();
    }

    /**
     * Universal login
     *
     * @param string $user
     * @param string $password
     *
     * @return void
     */
    protected function login($user, $password)
    {
        $this->session->visit($this->getUrl() . 'login');
        $this->assertNotNull($mainEl = $this->page->find('css', '#login_form'), 'Login form does not found');
        $this->assertNotNull($usernameEl = $this->page->find('css', '#username'));
        $usernameEl->setValue($user);
        $this->assertNotNull($passwordEl = $this->page->find('css', '#password'));
        $passwordEl->setValue($password);
        $this->page->pressButton('_submit');

        if (null !== $this->page->find('css', '#login_form #password')) {
            $this->fail("Login as '{$user}' with password '{$password}' failed");
        }
    }

    /**
     * Logout
     */
    protected function logout()
    {
        $this->session->visit($this->getUrl() . 'logout');
    }

    /**
     * Fill form
     *
     * @param \Behat\Mink\Element\NodeElement $form
     * @param array $fields
     */
    protected function fillForm(\Behat\Mink\Element\NodeElement $form, array $fields)
    {
        foreach ($fields as $field => $value) {
            try {
                $this->assertNotNull($fieldElement = $form->findField($field));
                if ('radio' == $fieldElement->getAttribute('type')) {
                    if (in_array($this->getMink()->getDefaultSessionName(), array('goutte', 'symfony'))) {
                        $fieldElement->click(); // FIXME it does not work
                    } else {
                        /* @var $selectList \Behat\Mink\Element\NodeElement */
                        $this->assertNotNull($radioLabel = $form->find('css', '#' . $field));
                        if ($radioLabel->isVisible()) {
                            $radioLabel->click();
                        } else {
                            $radioLabel->getParent()->click();
                        }
                    }
                } elseif ('checkbox' == $fieldElement->getAttribute('type')) {
                    if ($value) {
                        $fieldElement->check();
                    } else {
                        $fieldElement->uncheck();
                    }
                } elseif ('select-one' == $fieldElement->getAttribute('type') ||
                    'select' == $fieldElement->getTagName()
                ) {
                    $fieldElement->selectOption($value);
                } else {
                    $i = 6;
                    while (true) {
                        try {
                            $form->fillField($field, $value);
                            $this->assertEquals($value, $fieldElement->getValue());
                            break;
                        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                            if ($i--) {
                                $form->setValue($field, '');
                                $form->fillField($field, '');
                                sleep(1);
                            } else {
                                $this->fail("Value '{$value}' did not set to field '{$field}'");
                            }
                        }
                    }
                }
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                $this->assertNotNull(
                    $fieldElement = $form->find('css', '#' . $field . '_link'),
                    "Fields '{$field}' or '{$field}_link' have not been found"
                );
                $fieldElement->click();

                /* @var $selectList \Behat\Mink\Element\NodeElement */
                $this->assertNotNull($selectList = $this->page->find('css', '#' . $field . '_list'));
                /* @var $valueElement \Behat\Mink\Element\NodeElement */
                $this->assertNotNull(
                    $valueElement = $selectList->find('xpath', "/li/span[text()='{$value}']"),
                    "Value '{$value}' has not been found in select '{$field}'"
                );
                $valueElement->click();
            }
        }
    }

    /**
     * Retrieve absolute url or false
     *
     * @param string $text
     * @param string $postfix
     *
     * @return boolean|string
     */
    protected function retrieveAbsoluteUrl($text, $postfix = '')
    {
        $matches = array();
        if (0 === preg_match("/https?:\/\/{$postfix}[^ \n]*/", $text, $matches)) {
            return false;
        }
        if (empty($matches[0])) {
            return false;
        }
        $url = $matches[0];
        if ('http' != substr($url, 0, 4)) {
            return false;
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    protected function onNotSuccessfulTest(\Exception $e)
    {
        if ('selenium2' == static::getMink()->getDefaultSessionName() &&
            !in_array(
                get_class($e),
                array('PHPUnit_Framework_IncompleteTestError', 'PHPUnit_Framework_SkippedTestError')
            )
        ) {
            $name = '/logs/screenshot/' . date('Y-m-d_H:i:s') . '.png';
            file_put_contents(
                static::getContainer()->getParameter('web.upload.dir') . $name,
                static::getMink()->getSession()->getDriver()->getScreenshot()
            );

            $e = new \ErrorException(
                $e->getMessage() . ' http://' . static::getContainer()->getParameter('server_name') .
                '/uploads' . $name,
                $e->getCode(),
                0,
                $e->getFile(),
                $e->getLine() - 1, /* @link http://php.net/manual/en/exception.getline.php#102225 */
                $e
            );
        }
        parent::onNotSuccessfulTest($e);
    }

    /**
     * Runs qUnit tests
     *
     * @param $url
     * @param $test
     */
    protected function qUnit($url, $test)
    {
        $this->session->visit($this->getUrl() . $url . "?qunit=test/{$test}TestCase.js");
        $this->session->wait(
            $this->timeout,
            "jQuery('#qunit #qunit-tests .counts').children().length > 0"
        );
        $this->assertNotNull(
            $errors = $this->page->find('css', '#qunit #qunit-tests .counts .failed'),
            'qUnit block does not loaded'
        );
        $this->assertEquals(0, $errors->getText(), 'Some qUnit errors detected');
    }

    /**
     * Accept alert
     */
    protected function acceptAlert()
    {
        if ('selenium2' == $this->getMink()->getDefaultSessionName()) {
            /** @var Selenium2Driver $driver */
            $driver = static::getMink()->getSession()->getDriver();
            $driver->getWebDriverSession()->accept_alert();
        }
    }

    /**
     * @param string $selectId
     * @param string $value
     */
    protected function chooseLinkSelect($selectId, $value)
    {
        $link = $this->page->find('css', sprintf('#%s_link', $selectId));
        $this->assertNotNull($link, sprintf('Link-select for select with id "%s" not found', $selectId));
        $link->click();

        $li = $this->page->find('css', sprintf('[id^=%s_li][data-value=%s]', $selectId, $value));
        $this->assertNotNull($li, sprintf('Option with value "%s" not found', $value));
        $li->click();
    }

    /**
     * @param string $cssSelector
     * @param string $failMessage
     *
     * @return \Behat\Mink\Element\NodeElement
     */
    protected function getDomElement($cssSelector, $failMessage = '')
    {
        $element = $this->page->find('css', $cssSelector);

        $message = $failMessage ?: sprintf('Element with css selector "%s" cannot be found.', $cssSelector);

        $this->assertNotNull($element, $message);

        return $element;
    }

    /**
     * @param string $cssSelector
     * @param string $failMessage
     *
     * @return \Behat\Mink\Element\NodeElement[]
     */
    public function getDomElements($cssSelector, $failMessage = '')
    {
        $elements = $this->page->findAll('css', $cssSelector);

        $message = $failMessage ?: sprintf('Elements with css selector "%s" cannot be found.', $cssSelector);

        $this->assertNotEmpty($elements, $message);

        return $elements;
    }
}
