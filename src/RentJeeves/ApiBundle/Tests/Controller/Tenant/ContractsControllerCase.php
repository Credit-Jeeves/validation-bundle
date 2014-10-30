<?php

namespace RentJeeves\ApiBundle\Tests\Controller\Tenant;

use RentJeeves\ApiBundle\Forms\Enum\ReportingType;
use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\DataBundle\Entity\Contract;

class ContractsControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'RjDataBundle:Contract';

    public static function getContractDataProvider()
    {
        return [
            [ 1, 'json', 200, true ],
            [ 2, 'json', 200, true ],
            [ 3, 'json', 200, false],
        ];
    }

    /**
     * @test
     * @dataProvider getContractDataProvider
     */
    public function getContract($id, $format, $statusCode, $checkBalance)
    {
        $client = $this->getClient();

        $encodedId = $this->getIdEncoder()->encode($id);

        $client->request(
            'GET',
            self::URL_PREFIX . "/contracts/{$encodedId}.{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::TENANT_ACCESS_TOKEN,
            ]
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        $answer = $this->parseContent($client->getResponse()->getContent(), $format);

        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        $tenant = $this->getTenant();

        /** @var Contract $result */
        $result = $repo->findOneBy(['tenant' => $tenant, 'id' => $id]);

        $this->assertNotNull($result);

        $this->assertEquals(
            $result->getId(),
            $this->getIdEncoder()->decode($answer['id'])
        );

        $this->assertEquals(
            $result->getId(),
            $this->getUrlEncoder()->decode($answer['url'])
        );

        $this->assertEquals(
            $result->getUnit()->getId(),
            $this->getUrlEncoder()->decode($answer['unit_url'])
        );

        $this->assertEquals(
            $result->getStatus(),
            $answer['status']
        );

        $this->assertEquals(
            number_format($result->getRent(), 2, '.', ''),
            $answer['rent']
        );

        $leaseStartResult = $result->getStartAt() ? $result->getStartAt()->format('Y-m-d') : '';

        $this->assertEquals(
            $leaseStartResult,
            $answer['lease_start']
        );

        $leaseEndResult = $result->getFinishAt() ? $result->getFinishAt()->format('Y-m-d') : '';

        $this->assertEquals(
            $leaseEndResult,
            $answer['lease_end']
        );

        $dueDateResult = $result->getDueDate() ?  $result->getDueDate() : '';

        $this->assertEquals(
            $dueDateResult,
            $answer['due_date']
        );

        $this->assertEquals(
            $result->getReportToExperian(),
            ReportingType::getMapValue($answer['experian_reporting'])
        );

        if ($checkBalance) {
            $this->assertEquals(
                number_format($result->getIntegratedBalance(), 2, '.', ''),
                $answer['balance']
            );
        } else {
            $this->assertTrue(!isset($answer['balance']));
        }
    }
}
