<?php

namespace RentJeeves\ApiBundle\Tests\RestApi\Tenant;

use JMS\Serializer\Serializer;
use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\DataBundle\Entity\Payment;

class PaymentControllerCase extends BaseApiTestCase
{
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

}