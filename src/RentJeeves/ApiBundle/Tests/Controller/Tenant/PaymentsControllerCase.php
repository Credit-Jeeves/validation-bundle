<?php

namespace RentJeeves\ApiBundle\Tests\Controller\Tenant;

use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\DataBundle\Entity\Payment as PaymentEntity;
use RentJeeves\DataBundle\Entity\PaymentRepository;
use RentJeeves\DataBundle\Enum\PaymentStatus;

class PaymentsControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'RjDataBundle:Payment';

    const REQUEST_URL = 'payments';

    public static function getPaymentsDataProvider()
    {
        return [
            ['tenant11@example.com'],
        ];
    }

    /**
     * @test
     * @dataProvider getPaymentsDataProvider
     */
    public function getPayments($email, $format = 'json', $statusCode = 200)
    {
        $this->setUserEmail($email);

        /** @var PaymentRepository $repo */
        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        $tenant = $this->getUser();
        $result = $repo->findByUser($tenant);

        $response = $this->getRequest(null, [], $format);

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
        $this->setUserEmail('tenant11@example.com');

        /** @var PaymentRepository $repo */
        $repo = $this->getEntityRepository('RjDataBundle:Payment');
        /** @var PaymentEntity $result */
        $result = $repo->findOneByIdForUser($id, $this->getUser());

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
                'day' => $date->modify('+ 1 day')->format('j'),
                'month' =>  $date->modify('+ 1 month')->format('n'),
                'year' => $date->format('Y'),
                'paid_for' =>  $date->format('Y-m')
            ],
            [
                'contract_url' => 'contract_url/1758512013',
                'payment_account_url' => 'payment_account_url/656765400',
                'type' =>  'recurring',
                'rent' =>  "600.00",
                'other' => "0.00",
                'day' => $date->modify('+ 2 day')->format('j'),
                'month' => $date->format('n'),
                'year' => $date->format('Y'),
                'paid_for' => $date->modify('+ 1 month')->format('Y-m')
            ]
        ];
    }

    public static function createPaymentDataProvider()
    {
        return [
            [
                self::paymentDataProvider()[0],
            ],
            [
                self::paymentDataProvider()[1]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createPaymentDataProvider
     */
    public function createPayment($requestParams, $format = 'json', $statusCode = 201)
    {
        $response = $this->postRequest($requestParams, $format);

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
        $paidForDay = $payment->getPaidFor()->format('j');
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
                $paymentsData[1]
            ],
            [
                $paymentsData[0]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider editPaymentDataProvider
     */
    public function editPayment($requestParams, $format = 'json', $statusCode = 204)
    {
        /** @var PaymentRepository $repo */
        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        $tenant = $this->getUser();
        $result = $repo->findByUser($tenant);
        /** @var PaymentEntity $latest */
        $latest = $result[0];

        $encodedId = $this->getIdEncoder()->encode($latest->getId());

        $response = $this->putRequest($encodedId, $requestParams, $format);

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
                self::paymentCommonData() + self::paymentNegativeDataProvider()[0],
                ['payment.start_date.error.past']
            ],
            [
                self::paymentCommonData() + self::paymentNegativeDataProvider()[1],
                ['payment.year.error.past', 'payment.start_date.error.past']
            ],
            [
                self::paymentCommonData() + self::paymentNegativeDataProvider()[2],
                ['contract.error.is_end_later_than_start', 'payment.end_year.error.past']
            ],
            [
                self::paymentCommonData() + self::paymentNegativeDataProvider()[3],
                ['error.contract.paid_for']
            ],
            [
                self::paymentCommonData() + self::paymentNegativeDataProvider()[4],
                ['This value should be 12 or less.']
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createPaymentNegativeDataProvider
     */
    public function errorResponse($requestParams, $errorMessage, $format = 'json', $statusCode = 400)
    {
        $date = new \DateTime();

        $requestParams['day'] = $date->modify("{$requestParams['day']}  day")->format('j');

        $requestParams['month'] =
            $requestParams['month'] ?:
                $date->modify("{$requestParams['month']}  month")->format('n');

        $requestParams['year'] = $date->modify("{$requestParams['year']}  year")->format('Y');

        $requestParams['paid_for'] =
            $requestParams['paid_for'] ?:
                $date->format('Y') . '-' .   $date->modify("+ 1 month")->format('m');

        if (isset($requestParams['end_month']) && isset( $requestParams['end_year'])) {
            $requestParams['end_month'] =  $date->modify("{$requestParams['end_month']}  month")->format('n');
            $requestParams['end_year'] = $date->modify("{$requestParams['end_year']}  year")->format('Y');
        }

        $response = $this->postRequest($requestParams, $format);

        $this->assertResponse($response, $statusCode, $format);

        $responseContent = $this->parseContent($response->getContent(), $format);

        $this->assertCount(count($errorMessage), $responseContent, 'wrong count error');

        $errorContent = [];
        foreach ($responseContent as $key => $value) {
            $errorContent[$value['message']] = $value['message'];
        }

        $this->assertCount(0, array_diff($errorContent, $errorMessage), 'wrong count error');
    }

    /**
     * @test
     */
    public function deletePayment()
    {
        $id = 1;
        $this->setUserEmail('tenant11@example.com');

        /** @var PaymentRepository $repo */
        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        /** @var PaymentEntity $result */
        $payment = $repo->findOneByIdForUser($id, $this->getUser());
        $this->assertEquals(PaymentStatus::ACTIVE, $payment->getStatus());

        $response = $this->deleteRequest($this->getIdEncoder()->encode($id));
        $answer = $this->parseContent($response->getContent());
        $this->assertResponse($response);

        $this->assertEquals(true, $answer['result']);

        $this->getEm()->refresh($payment);
        $this->assertEquals(PaymentStatus::CLOSE, $payment->getStatus());

        $response = $this->deleteRequest($this->getIdEncoder()->encode($id));
        $answer = $this->parseContent($response->getContent());
        $this->assertResponse($response);

        $this->assertEquals(false, $answer['result']);
        $this->assertEquals('Payment is already closed.', $answer['message']);
    }
}
