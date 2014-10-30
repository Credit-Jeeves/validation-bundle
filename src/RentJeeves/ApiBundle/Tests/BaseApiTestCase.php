<?php

namespace RentJeeves\ApiBundle\Tests;

use Doctrine\ORM\EntityManager;
use FOS\OAuthServerBundle\Entity\Client;
use OAuth2\IOAuth2Storage;
use RentJeeves\ApiBundle\Services\Encoders\AttributeEncoderInterface;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\TestBundle\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\Serializer;

class BaseApiTestCase extends BaseTestCase
{
    const URL_PREFIX = '/api/tenant/v1';

    const TENANT_ACCESS_TOKEN = 'api_tenant_test_case';

    protected static $instance = false;

    protected static $formats = [
        'html'=> ['text/html','application/xhtml+xml'],
        'json'=> ['application/json','application/x-json'],
        'xml'=> ['text/xml','application/xml','application/x-xml'],
    ];

    /** @var  EntityManager */
    private $em;

    /** @var  AttributeEncoderInterface */
    private $idEncoder;

    /** @var  AttributeEncoderInterface */
    private $urlEncoder;

    private $tenantEmail= 'tenant11@example.com';

    protected function assertResponse(Response $response, $statusCode = 200, $format = 'json')
    {
        $this->assertEquals(
            $statusCode,
            $response->getStatusCode(),
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

    protected function parseContent($content, $format = 'json')
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

        $repo = $this->getEntityRepository('DataBundle:Client');
        /** @var Client $oauthClient */
        $oauthClient = $repo->find(1);

        $oauthStorage->createAccessToken(
            static::TENANT_ACCESS_TOKEN,
            $oauthClient,
            $this->getTenant(),
            0
        );
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

    protected function getEntityRepository($entityPath)
    {
        if (!$this->em) {
            $this->em = $this->getContainer()->get('doctrine')->getManager();
        }

        return $this->em->getRepository($entityPath);
    }

    protected function setTenantEmail($email)
    {
        $this->tenantEmail = $email;
        static::$instance = false;
    }

    protected function getTenantEmail()
    {
        return $this->tenantEmail;
    }

    /**
     * @return null|Tenant
     */
    protected function getTenant()
    {
        return $this
            ->getEntityRepository('RjDataBundle:Tenant')
            ->findOneBy(['email' => $this->tenantEmail]);
    }

    /**
     * @param string $idEncoderServiceId
     * @param bool $refresh
     * @return AttributeEncoderInterface
     */
    protected function getIdEncoder($idEncoderServiceId = 'api.default_id_encoder', $refresh = false)
    {
        if ($refresh || !$this->idEncoder) {
            $this->idEncoder = $this->getContainer()->get($idEncoderServiceId);
        }

        return $this->idEncoder;
    }

    /**
     * @param string $urlEncoderServiceId
     * @param bool $refresh
     * @return AttributeEncoderInterface
     */
    protected function getUrlEncoder($urlEncoderServiceId = 'api.default_url_encoder', $refresh = false)
    {
        if ($refresh || !$this->urlEncoder) {
            $this->urlEncoder = $this->getContainer()->get($urlEncoderServiceId);
        }

        return $this->urlEncoder;
    }
}
