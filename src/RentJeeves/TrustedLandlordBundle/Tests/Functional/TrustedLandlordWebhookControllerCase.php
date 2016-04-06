<?php
namespace RentJeeves\PublicBundle\Tests\Functional;

use RentJeeves\CoreBundle\HttpClient\HttpClient;
use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Entity\TrustedLandlordJiraMapping;
use RentJeeves\DataBundle\Enum\TrustedLandlordStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class TrustedLandlordWebhookControllerCaseCase extends BaseTestCase
{
    /**
     * @test
     * @expectedException Guzzle\Http\Exception\ClientErrorResponseException
     */
    public function shouldFailedPostWebhookWhenDoNotHaveJiraMapping()
    {
        $this->load(true);
        /** @var HttpClient $client */
        $client = $this->getContainer()->get('http_client');
        $body = $this->getJiraRequestMock();
        $link = $this->getUrl() . 'trusted-landlord/jira-webhook/RT';
        $client->send(
            'POST',
            $link,
            ['Content-Type' => 'application/json'],
            $body
        );
    }

    /**
     * #test
     */
    public function shouldSuccessPostWebhookWhenWeHaveJiraMapping()
    {
        $this->load(true);
        $landlord = $this->getEntityManager()->getRepository('RjDataBundle:TrustedLandlord')->find(1);
        $this->assertEquals(TrustedLandlordStatus::NEWONE, $landlord->getStatus(), 'Wrong status in fixtures');
        $mapping = new TrustedLandlordJiraMapping();
        $mapping->setTrustedLandlord($landlord);
        $mapping->setJiraKey('TLDTEST-7');
        $this->getEntityManager()->persist($mapping);
        $this->getEntityManager()->flush();
        /** @var HttpClient $client */
        $client = $this->getContainer()->get('http_client');
        $body = $this->getJiraRequestMock();
        $link = $this->getUrl() . 'trusted-landlord/jira-webhook/RT';
        $client->send(
            'POST',
            $link,
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            $body
        );
        $this->getEntityManager()->refresh($landlord);
        $this->assertEquals(
            TrustedLandlordStatus::WAITING_FOR_INFO,
            $landlord->getStatus(),
            'Wrong status set for landlord'
        );

    }

    /**
     * @return string XML data from MRI
     */
    protected function getJiraRequestMock()
    {
        $pathToFile = $this->getFileLocator()->locate('@TrustedLandlordBundle/Resources/fixtures/jira_hook_data.txt');

        return file_get_contents($pathToFile);
    }

    /**
     * @return \Symfony\Component\HttpKernel\Config\FileLocator
     */
    protected function getFileLocator()
    {
        return $this->getContainer()->get('file_locator');
    }
}
