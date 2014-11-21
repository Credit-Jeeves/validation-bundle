<?php

namespace RentJeeves\ApiBundle\Tests\Controller\Tenant;

use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\DataBundle\Entity\Payment as PaymentEntity;
use RentJeeves\DataBundle\Entity\PaymentRepository;

class PaymentControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'RjDataBundle:Payment';

    const REQUEST_URL = 'payments';

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

        $this->getClient();

        /** @var PaymentRepository $repo */
        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        $tenant = $this->getTenant();
        $result = $repo->findByUser($tenant);

        $response = $this->getRequest();

        $this->assertResponse($response, $statusCode, $format);

        $answer = $this->parseContent($response->getContent(), $format);

        $this->assertCount(count($result), $answer);

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
        $this->getClient();

        /** @var PaymentRepository $repo */
        $repo = $this->getEntityRepository('RjDataBundle:Payment');
        /** @var PaymentEntity $result */
        $result = $repo->findOneByIdForUser($id, $this->getTenant());

        $response = $this->getRequest($this->getIdEncoder()->encode($id));

        $answer = $this->parseContent($response->getContent());

        $this->assertResponse($response);

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
        $this->assertEquals(
            $result->getContract()->getId(),
            $this->getUrlEncoder()->decode($answer["contract_url"])
        );
        $this->assertEquals(
            $result->getPaymentAccountId(),
            $this->getUrlEncoder()->decode($answer["payment_account_url"])
        );
    }


    public static function paymentDataProvider()
    {

        $date = new \DateTime();

        return [
            [
                'contract_url' => 'contract_url/1758512013',
                'payment_account_url' => 'payment_account_url/656765400',
                'type' =>  'one_time',
                'rent' =>  1200.00,
                'other' => 75.00,
                'day' => $date->modify('+ 1 day')->format('d'),
                'month' =>  $date->modify('+ 1 month')->format('m'),
                'year' => $date->format('Y'),
                'paid_for' =>  $date->format('Y-m')
            ],
            [
                'contract_url' => 'contract_url/1758512013',
                'payment_account_url' => 'payment_account_url/656765400',
                'type' =>  'recurring',
                'rent' =>  "600.00",
                'other' => "0.00",
                'day' => $date->modify('+ 2 day')->format('d'),
                'month' => $date->format('m'),
                'year' => $date->format('Y'),
                'paid_for' => $date->modify('+ 1 month')->format('Y-m')
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
        $response = $this->postRequest($requestParams);

        $this->assertResponse($response, $statusCode, $format);

        $answer = $this->parseContent($response->getContent(), $format);

        /** @var PaymentRepository $repo */
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

    public static function editPaymentDataProvider()
    {
        $paymentsData = self::paymentDataProvider();
        foreach ($paymentsData as $key => $paymentData) {
            unset ($paymentData['contract_url']);
            $paymentsData[$key] = $paymentData;
        }

        return [
            [
                'json',
                204,
                $paymentsData[1]
            ],
            [
                'json',
                204,
                $paymentsData[0]
            ]
        ];
    }

    /**
     * @test
     * @depends createPayment
     * @dataProvider editPaymentDataProvider
     */
    public function editPayment($format, $statusCode, $requestParams)
    {
        /** @var PaymentRepository $repo */
        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        $tenant = $this->getTenant();
        $result = $repo->findByUser($tenant);
        /** @var PaymentEntity $latest */
        $latest = $result[0];

        $encodedId = $this->getIdEncoder()->encode($latest->getId());

        $response = $this->putRequest($encodedId, $requestParams);

        $this->assertResponse($response, $statusCode, $format);

        $this->getEm()->refresh($latest);

        $this->assertEquals($latest->getType(), $requestParams['type']);
        $this->assertEquals($latest->getAmount(), $requestParams['rent']);
    }

    // common data for negative result
    public static function paymentCommonData()
    {
        return[
            'contract_url' => 'contract_url/1758512013',
            'payment_account_url' => 'payment_account_url/656765400',
            'type' =>  'one_time',
            'rent' =>  1200.00,
            'other' => 75.00
        ];
    }

    // data for negative result
    public static function paymentNegativeDataProvider()
    {
        return [
            [
                'day' => '- 2',
                'month' => '0',
                'year' => '0',
                'paid_for' => null
            ],
            [
                'day' => '0',
                'month' => '0',
                'year' => '- 2',
                'paid_for' => null
            ],
            [
                'day' => '+10',
                'month' => '0',
                'year' => '0',
                'end_month' => '0',
                'end_year' => '- 2',
                'paid_for' => null
            ],
            [
                'day' => '+ 2',
                'month' => '0',
                'year' => '0',
                'paid_for' => 'test'
            ],
            [
                'day' => '+ 2',
                'month' => '13',
                'year' => '0',
                'paid_for' => null
            ],
        ];
    }

    public static function createPaymentNegativeDataProvider()
    {
        return [
            [
                'json',
                400,
                self::paymentCommonData() + self::paymentNegativeDataProvider()[0],
                ['payment.start_date.error.past']
            ],
            [
                'json',
                400,
                self::paymentCommonData() + self::paymentNegativeDataProvider()[1],
                ['payment.year.error.past', 'payment.start_date.error.past']
            ],
            [
                'json',
                400,
                self::paymentCommonData() + self::paymentNegativeDataProvider()[2],
                ['contract.error.is_end_later_than_start', 'payment.end_year.error.past']
            ],
            [
                'json',
                400,
                self::paymentCommonData() + self::paymentNegativeDataProvider()[3],
                ['error.contract.paid_for']
            ],
            [
                'json',
                400,
                self::paymentCommonData() + self::paymentNegativeDataProvider()[4],
                ['This value should be 12 or less.']
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createPaymentNegativeDataProvider
     */
    public function errorResponse($format, $statusCode, $requestParams, $errorMessage)
    {
        $date = new \DateTime();

        $requestParams['day'] = $date->modify("{$requestParams['day']}  day")->format('d');
        $requestParams['month'] = $requestParams['month'] ? $requestParams['month'] : $date->modify("{$requestParams['month']}  month")->format('m');
        $requestParams['year'] = $date->modify("{$requestParams['year']}  year")->format('Y');

        $requestParams['paid_for'] =
            $requestParams['paid_for'] ?
                $requestParams['paid_for'] :
                $date->format('Y') . '-' .   $date->modify("+ 1 month")->format('m');

        if (isset($requestParams['end_month']) && isset( $requestParams['end_year'])) {
            $requestParams['end_month'] =  $date->modify("{$requestParams['end_month']}  month")->format('m');
            $requestParams['end_year'] = $date->modify("{$requestParams['end_year']}  year")->format('Y');

        }

        $response = $this->postRequest($requestParams);

        $this->assertResponse($response, $statusCode, $format);

        $responseContent = $this->parseContent($response->getContent(), $format);

        $this->assertCount(count($errorMessage), $responseContent, 'wrong count error');

        $errorContent = [];
        foreach ($responseContent as $key=>$value) {
            $errorContent[$value['message']] = $value['message'];
        }

        $this->assertCount(0, array_diff($errorContent, $errorMessage ), 'wrong count error');
    }
}
