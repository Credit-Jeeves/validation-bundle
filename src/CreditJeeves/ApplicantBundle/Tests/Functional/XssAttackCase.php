<?php
namespace CreditJeeves\ApplicantBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class XssAttackCase extends BaseTestCase
{

    /**
     * @test
     */
    public function sendGetRequest()
    {
        $this->markTestSkipped('We don\'t use sf1 anymore. Need write new test for xss hack.');
        $this->setDefaultSession('goutte');
        require_once __DIR__ . '/../../../CoreBundle/sfConfig.php';
        require_once __DIR__ . '/../../../../../vendor/credit-jeeves/credit-jeeves/lib/curl/Curl.class.php';
        $curl = new \Curl('');
        $url = $this->getUrl() . '>"><script>alert(123)</script><"/_test.php/_test.php/';
        $curl->setUrl($url);
        $resp = $curl->sendGetRequest('');
        $count = preg_match_all('/<script>(.*)<\/script>/', $resp, $matches) ? $matches : array();
        $this->assertCount(0, $count, 'Possibility of XSS attack is detected by url: ' . $url);
    }
}
