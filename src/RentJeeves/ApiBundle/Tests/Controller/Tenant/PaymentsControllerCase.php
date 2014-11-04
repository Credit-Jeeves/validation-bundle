<?php

namespace RentJeeves\ApiBundle\Tests\Controller\Tenant;

use JMS\Serializer\Serializer;
use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\DataBundle\Entity\Payment as PaymentEntity;

class PaymentControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'RjDataBundle:Payment';

    public static function getPaymentsDataProvider()
    {
        return [
            ['json', 200, 'tenant11@example.com'],
        ];
    }

    /**
     * @test
     * @dataProvider getPaymentsDataProvider
     */
    public function getPayments($format, $statusCode, $email)
    {
        $this->setTenantEmail($email);

        $client = $this->getClient();

        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        $tenant = $this->getTenant();
        $result = $repo->findByUser($tenant);

        $client->request(
            'GET',
            self::URL_PREFIX . "/payments.{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::TENANT_ACCESS_TOKEN,
            ]
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        $answer = $this->parseContent($client->getResponse()->getContent(), $format);

        $this->assertEquals(count($result), count($answer));

        // check first and last element
        $this->assertEquals(
            $result[0]->getId(),
            $this->getIdEncoder()->decode($answer[0]['id'])
        );

        $this->assertEquals(
            $result[0]->getId(),
            $this->getUrlEncoder()->decode($answer[0]['url'])
        );

        $this->assertEquals(
            $result[count($result)-1]->getId(),
            $this->getUrlEncoder()->decode($answer[count($answer) -1]['url'])
        );

        $this->assertEquals(
            $this->getIdEncoder()->encode($result[count($result)-1]->getId()),
            $answer[count($answer) -1]['id']
        );
    }

    /**
     * @test
     */
    public function getPayment()
    {
        $id = 1;
        $this->setTenantEmail('tenant11@example.com');

        $client = $this->getClient();
        $repo = $this->getEntityRepository('RjDataBundle:Payment');
        $result = $repo->findOneByIdForUser($id, $this->getTenant());

        $client->request(
            'GET',
            self::URL_PREFIX . "/payments/" . $this->getIdEncoder()->encode($id),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::TENANT_ACCESS_TOKEN,
            ]
        );
        $answer = $this->parseContent($client->getResponse()->getContent());

        $this->assertResponse($client->getResponse());
        $this->assertEquals($result->getType(), $answer["type"]);
        $this->assertEquals($result->getAmount(), $answer["rent"]);
        $this->assertEquals("0.00", $answer["other"]);
        $this->assertEquals($result->getDueDate(), $answer["day"]);
        $this->assertEquals($result->getStartMonth(), $answer["month"]);
        $this->assertEquals($result->getStartYear(), $answer["year"]);
        $this->assertEquals($result->getEndMonth(), $answer["end_month"]);
        $this->assertEquals($result->getEndYear(), $answer["end_year"]);
        $this->assertEquals($result->getPaidFor()->format("Y-m"), $answer["paid_for"]);
        $this->assertEquals($result->getStatus(), $answer["status"]);
        $this->assertEquals($result->getContract()->getId(), $answer["contract_url"]);
        $this->assertEquals($result->getPaymentAccountId(), $answer["payment_account_url"]);
    }


    public static function paymentDataProvider()
    {
        return [
            [
                'contract_url' => 'contract_url/1758512013',
                'payment_account_url' => 'payment_account_url/656765400',
                'type' =>  'one_time',
                'rent' =>  1200.00,
                'other' => 75.00,
                'day' => 1,
                'month' => 1,
                'year' => 2014,
                'paid_for' => '2014-08'
            ],
            [
                'contract_url' => 'contract_url/1758512013',
                'payment_account_url' => 'payment_account_url/656765400',
                'type' =>  'recurring',
                'rent' =>  "600.00",
                'other' => "0.00",
                'day' => 3,
                'month' => 10,
                'year' => 2014,
                'paid_for' => '2014-10'
            ]
        ];
    }

    public static function createPaymentDataProvider()
    {
        return [
            [
                'json',
                201,
                self::paymentDataProvider()[0],
            ],
            [
                'json',
                201,
                self::paymentDataProvider()[1]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createPaymentDataProvider
     */
    public function createPayment($format, $statusCode, $requestParams)
    {
        #$this->setTenantEmail('tenant11@example.com');
        $client = $this->getClient();

        /** @var Serializer $serializer */
        $serializer = $this->getContainer()->get('jms_serializer');

        $client->request(
            'POST',
            self::URL_PREFIX . "/payments.{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::TENANT_ACCESS_TOKEN,
            ],
            $serializer->serialize($requestParams, $format)
        );
        $this->assertResponse($client->getResponse(), $statusCode, $format);

        $answer = $this->parseContent($client->getResponse()->getContent(), $format);
        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        /** @var PaymentEntity $payment */
        $payment = $repo->findOneBy([
            'id' => $this->getIdEncoder()->decode($answer['id'])
        ]);
        $this->assertNotNull($payment);

        /* paidFor day shall match contract dueDate */
        $dueDay = $payment->getContract()->getDueDate();
        $paidForDay = $payment->getPaidFor()->format('d');
        $this->assertEquals($dueDay, $paidForDay);
    }
}
