<?php

namespace RentJeeves\ApiBundle\Tests;

use FOS\OAuthServerBundle\Entity\Client;
use OAuth2\IOAuth2Storage;
use RentJeeves\DataBundle\Entity\LandlordRepository;
use RentJeeves\TestBundle\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\Serializer;

class BaseApiTestCase extends BaseTestCase
{
    const ACCESS_TOKEN = 'test';

    protected static $instance = false;

    protected static $formats = [
        'html'=> ['text/html','application/xhtml+xml'],
        'json'=> ['application/json','application/x-json'],
        'xml'=> ['text/xml','application/xml','application/x-xml'],
    ];

    protected function assertResponse(Response $response, $statusCode = 200, $format = 'html')
    {
        $this->assertEquals(
            $statusCode, $response->getStatusCode(),
            $response->getContent()
        );

        $contentType = $response->headers->get('Content-Type');

        $this->assertTrue(isset(static::$formats[$format]), "Content Type \"$contentType\" is not available.");

        $this->assertContains(
            $contentType,
            static::$formats[$format],
            $response->headers
        );
    }

    protected function assertResponseContent($content, $result, $format)
    {
        $data = $this->parseContent($content, $format);

        $this->assertEquals($result, $data, 'Response is incorrect.');
    }

    protected function parseContent($content, $format)
    {
        /** @var Serializer $serializer */
        $serializer = $this->getContainer()->get('jms_serializer');

        if ($content) {
            return $serializer->deserialize($content, 'array', $format);
        }

        return '';
    }

    protected function prepareOAuthAuthorization()
    {
        /** @var IOAuth2Storage $oauthStorage */
        $oauthStorage = $this->getContainer()->get('fos_oauth_server.storage');

        $em = $this->getContainer()->get('doctrine')->getManager();

        $repo = $em->getRepository('DataBundle:Client');
        /** @var Client $oauthClient */
        $oauthClient = $repo->find(1);
        /** @var LandlordRepository $repo */
        $repo = $em->getRepository('RjDataBundle:Landlord');

        $user = $repo->findOneBy(['email' => 'landlord1@example.com']);

        $oauthStorage->createAccessToken(static::ACCESS_TOKEN, $oauthClient, $user, 0);
    }

    protected function getClient()
    {
        if (self::$instance != true) {
            $this->load(true);
            $this->prepareOAuthAuthorization();
            self::$instance = true;
        }
        return parent::createClient();
    }
}
