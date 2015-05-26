<?php

namespace RentJeeves\ApiBundle\Tests\Controller\Tenant;

use RentJeeves\ApiBundle\Tests\BaseApiTestCase;

class TenantControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'RjDataBundle:Tenant';

    const REQUEST_URL = 'details';

    /**
     * @test
     */
    public function getDetails()
    {
        $this->prepareClient(true);

        $response = $this->getRequest();

        $this->assertResponse($response);

        $this->assertResponseContent(
            $response->getContent(),
            [
                'first_name' => 'TIMOTHY',
                'last_name' => 'APPLEGATE',
                'middle_name' => 'A',
                'email' => 'tenant11@example.com',
                'phone' => '7858655392',
                'date_of_birth' => '1937-11-10',
                'ssn' => '666-30-9041',
                'verify_status' => 'none',
                'verify_message' => '',
            ]
        );
    }

    /**
     * @return array
     */
    public function updateDetailsDataProvider()
    {
        return [
            [
                [
                    'first_name' => 'Timmy',
                    'last_name' => 'Applegate',
                    'phone' => '7858655392',
                    'date_of_birth' => '2001-10-10',
                    'ssn' => '666-30-9042',
                ],
            ],
            [
                [
                    'first_name' => 'Timmy',
                    'last_name' => 'Applegate',
                    'middle_name' => 'A',
                    'email' => 'tenant12@example.com',
                    'date_of_birth' => '1985-01-10',
                    'ssn' => '555-55-5555',
                ]
            ],
        ];
    }

    /**
     * @param array $requestParams
     *
     * @test
     * @dataProvider updateDetailsDataProvider
     */
    public function updateDetails($requestParams)
    {
        $oldUser = clone $this->getUser();

        $response = $this->putRequest(null, $requestParams);

        $this->assertResponse($response, 204);

        $this->getEm()->refresh($this->getUser());

        $this->assertNotEquals($oldUser, $this->getUser());

        $this->assertEquals($oldUser->getEmail(), $this->getUser()->getEmail());
    }

    /**
     * @test
     */
    public function updateFullDetails()
    {
        $oldUser = clone $this->getUser();

        $requestParams = [
            'first_name' => 'Test First Name',
            'last_name' => 'Test Last Name',
            'middle_name' => 'Test Middle Name',
            'phone' => '8997841255',
            'email' => 'email@ggggmc.com',
            'date_of_birth' => '1955-12-12',
            'ssn' => '521-23-8779',
        ];

        $response = $this->putRequest(null, $requestParams);

        $this->assertResponse($response, 204);

        $this->getEm()->refresh($this->getUser());

        $this->assertEquals($requestParams['first_name'], $this->getUser()->getFirstName());
        $this->assertEquals($requestParams['last_name'], $this->getUser()->getLastName());
        $this->assertEquals($requestParams['middle_name'], $this->getUser()->getMiddleInitial());
        $this->assertEquals($requestParams['phone'], $this->getUser()->getPhone());
        $this->assertEquals($oldUser->getEmail(), $this->getUser()->getEmail()); // should stay read only
        $this->assertEquals($requestParams['date_of_birth'], $this->getUser()->getDateOfBirth()->format('Y-m-d'));
        $this->assertEquals($requestParams['ssn'], $this->getUser()->getFormattedSsn());
    }
}
