<?php

namespace RentJeeves\ApiBundle\Tests;

use CreditJeeves\DataBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use FOS\OAuthServerBundle\Entity\Client;
use OAuth2\IOAuth2Storage;
use RentJeeves\ApiBundle\Services\Encoders\AttributeEncoderInterface;
use RentJeeves\TestBundle\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\Serializer;

class BaseApiTestCase extends BaseTestCase
{
    const URL_PREFIX = '/api/tenant';

    const USER_ACCESS_TOKEN = 'api_tenant_test_case';

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

    /** @var string */
    protected $userEmail= 'tenant11@example.com';

    /** @var  User */
    protected $user;

    /** @var \Symfony\Bundle\FrameworkBundle\Client */
    protected static $client;

    public function setUp()
    {
        $this->prepareClient();
    }

    protected function assertResponse(Response $response, $statusCode = 200, $format = 'json')
    {
        $this->assertEquals(
            $statusCode,
            $response->getStatusCode(),
            $response->getContent()
        );

        // Added because
        //  - no necessary check it for no-content response
        //  - Symfony 2.4.10 doesn't return content-type for no-content response
        if ($statusCode != 204) {
            $contentType = $response->headers->get('Content-Type');

            $this->assertTrue(isset(static::$formats[$format]), "Content Type \"$contentType\" is not available.");

            $this->assertContains(
                $contentType,
                static::$formats[$format],
                $response->headers
            );
        }
    }

    protected function assertResponseContent($content, $result, $format = 'json')
    {
        $data = $this->parseContent($content, $format);

        $this->assertEquals($result, $data, 'Response is incorrect.');
    }

    protected function assertFullUrl($url)
    {
        $urlInfo = parse_url($url);

        $this->assertTrue(isset($urlInfo['scheme']));

        $this->assertTrue(isset($urlInfo['host']));

        $this->assertTrue(isset($urlInfo['path']));
    }

    /**
     * @param  string       $content
     * @param  string       $format
     * @return array|string
     */
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

        if (!$oauthStorage->getAccessToken(static::USER_ACCESS_TOKEN . $this->getUser()->getEmail())) {
            $oauthStorage->createAccessToken(
                static::USER_ACCESS_TOKEN . $this->getUser()->getEmail(),
                $oauthClient,
                $this->getUser(),
                0
            );
        }
    }

    protected function prepareClient()
    {
        if (!static::$client) {
            $this->load(true);
            static::$client = static::$client ?: $this->createClient();
        }
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function getClient()
    {
        $this->prepareClient();

        $this->prepareOAuthAuthorization();

        return static::$client;
    }

    /**
     * @return EntityManager
     */
    protected function getEm()
    {
        if (!$this->em) {
            $this->em = $this->getContainer()->get('doctrine')->getManager();
        }

        return $this->em;
    }

    /**
     * @param  string                                                                       $entityPath
     * @return \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    protected function getEntityRepository($entityPath)
    {
        return $this->getEm()->getRepository($entityPath);
    }

    /**
     * @param string $email
     */
    protected function setUserEmail($email)
    {
        $this->userEmail = $email;
        /* Reload client */
        $this->user = null;
    }

    /**
     * @return string
     */
    protected function getUserEmail()
    {
        return $this->userEmail;
    }

    /**
     * @return null|User
     */
    protected function getUser()
    {
        return $this->user ?: $this->user = $this
            ->getEntityRepository('DataBundle:User')
            ->findOneBy(['email' => $this->getUserEmail()]);
    }

    /**
     * @param  string                    $idEncoderServiceId
     * @param  bool                      $refresh
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
     * @param  string                    $urlEncoderServiceId
     * @param  bool                      $refresh
     * @return AttributeEncoderInterface
     */
    protected function getUrlEncoder($urlEncoderServiceId = 'api.default_url_encoder', $refresh = false)
    {
        if ($refresh || !$this->urlEncoder) {
            $this->urlEncoder = $this->getContainer()->get($urlEncoderServiceId);
        }

        return $this->urlEncoder;
    }

    /**
     * @param  array         $requestParams
     * @param  string        $format
     * @return null|Response
     */
    protected function postRequest(array $requestParams = [], $format = 'json')
    {
        return $this->request(null, 'POST', $format, null, $requestParams);
    }

    /**
     * @param  null|string   $attributes
     * @param  array         $requestParams
     * @param  string        $format
     * @return null|Response
     */
    protected function putRequest($attributes = null, array $requestParams = [], $format = 'json')
    {
        return $this->request(null, 'PUT', $format, $attributes, $requestParams);
    }

    /**
     * @param  null          $attributes
     * @param  array         $requestParams
     * @param  string        $format
     * @return null|Response
     */
    protected function getRequest($attributes = null, array $requestParams = [], $format = 'json')
    {
        return $this->request(null, 'GET', $format, $attributes, $requestParams);
    }

    /**
     * @param  null|string   $attributes
     * @param  array         $requestParams
     * @param  string        $format
     * @return null|Response
     */
    protected function deleteRequest($attributes = null, array $requestParams = [], $format = 'json')
    {
        return $this->request(null, 'DELETE', $format, $attributes, $requestParams);
    }

    /**
     * @param  string|null   $fullUrl
     * @param  string        $method
     * @param  string        $format
     * @param  string|null   $attributes
     * @param  array         $requestParams
     * @return null|Response
     */
    protected function request(
        $fullUrl = null,
        $method = 'GET',
        $format = 'json',
        $attributes = null,
        array $requestParams = []
    ) {
        /** @var Serializer $serializer */
        $serializer = $this->getContainer()->get('jms_serializer');

        $client = $this->getClient();

        $params = ($method == 'GET') ? $requestParams : [];

        $client->request(
            $method,
            $fullUrl ?: $this->prepareUrl($attributes),
            $params,
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::USER_ACCESS_TOKEN . $this->getUser()->getEmail(),
            ],
            ($method != 'GET') ? $serializer->serialize($requestParams, $format) : null
        );

        return $client->getResponse();
    }

    /**
     * @param  null|int    $id
     * @param  string|bool $format
     * @param  string      $requestUrl
     * @param  bool        $isAbsolutePath
     * @return string
     */
    protected function prepareUrl($id = null, $format = 'json', $requestUrl = '', $isAbsolutePath = false)
    {
        $requestUrl = $requestUrl ?: static::REQUEST_URL;

        $siteUrl = '';
        if ($isAbsolutePath) {
            $siteUrl = $this->getSiteUrl();
        }

        return sprintf(
            '%s%s/%s%s%s',
            $siteUrl,
            static::URL_PREFIX,
            $requestUrl,
            $id ? '/' . $id : '',
            $format ? '.' . $format : ''
        );
    }

    /**
     * @return string
     */
    protected function getSiteUrl()
    {
        $https = $this->getClient()->getServerParameter('HTTPS', 'off');
        $port = $this->getClient()->getServerParameter('SERVER_PORT', 80);
        $protocol = ($https !== 'off' || $port == 443) ? "https" : "http";
        $domainName = $this->getClient()->getServerParameter('HTTP_HOST');

        return sprintf('%s://%s', $protocol, $domainName);
    }
}
