<?php

namespace RentJeeves\ApiBundle\Tests\Controller\Tenant;

use CreditJeeves\DataBundle\Entity\OrderRepository;
use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\CoreBundle\DateTime;

class OrdersControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'DataBundle:Order';

    const REQUEST_URL = 'orders';

    /**
     * @return array
     */
    public static function getEmptyOrdersDataProvider()
    {
        return [
            ['alex@rentrack.com'],
        ];
    }

    /**
     * @param string $email
     * @param int    $statusCode
     *
     * @test
     * @dataProvider getEmptyOrdersDataProvider
     */
    public function getEmptyOrders($email, $statusCode = 204)
    {
        $this->setUserEmail($email);

        $response = $this->getRequest();

        $this->assertResponse($response, $statusCode);
    }

    public static function getOrdersDataProvider()
    {
        return [
            ['tenant11@example.com'],
        ];
    }

    /**
     * @param string $email
     *
     * @test
     * @dataProvider getOrdersDataProvider
     */
    public function getOrders($email)
    {
        $this->setUserEmail($email);
        /** @var OrderRepository $repo */
        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        $tenant = $this->getUser();
        $result = $repo->getUserOrders($tenant);

        $response = $this->getRequest();

        $this->assertResponse($response);

        $answer = $this->parseContent($response->getContent());

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
            $result[0]->getStatus(),
            $answer[0]['status']
        );

        $this->assertEquals(
            $result[count($result)-1]->getId(),
            $this->getUrlEncoder()->decode($answer[count($answer) -1]['url'])
        );

        $this->assertEquals(
            $this->getIdEncoder()->encode($result[count($result)-1]->getId()),
            $answer[count($answer) -1]['id']
        );

        $this->assertEquals(
            $result[count($result) -1]->getStatus(),
            $answer[count($answer) -1]['status']
        );
    }

    /**
     * @return array
     */
    public static function getOrderDataProvider()
    {
        return [
            [2, 'card', '123123', 1500, 0, 1500, '', '9', '-50 days'], // OrderPayDirect
            [3, 'card', '456456', 1500, 300, 1800, '-41 days', '', '-40 days'],
            [5, 'card', '456555', 3000, 0, 3000, '', '', 'now', 'Heartland Error'],
            [6, 'card', '147741', 1500, 0, 1500, '', '', 'now'],
            [7, 'card', '55123260', 700, 0, 700, '-28 days', '', '-30 days', 'Payment was refunded'],
            [9, 'bank', '571232603', 800, 0, 800, '', '', '-30 days'],
            [14, 'card', '369369', 1500, 0, 1500, '-359 days', '9', '-370 days', '', true],
        ];
    }

    /**
     * @param int    $orderId
     * @param string $type
     * @param string $transactionId
     * @param string $depositedAt
     * @param string $paymentAccountId
     * @param string $paidFor
     * @param string $message
     * @param bool   $deletePaymentAccount
     *
     * @test
     * @dataProvider getOrderDataProvider
     * @see https://credit.atlassian.net/browse/RT-830 test list
     */
    public function getOrder(
        $orderId,
        $type,
        $transactionId,
        $rent,
        $other,
        $total,
        $depositedAt = '',
        $paymentAccountId = '',
        $paidFor = '',
        $message = '',
        $deletePaymentAccount = false
    ) {
        $paymentSource = '';
        /** Prepare Payment Account */
        if ($paymentAccountId && $deletePaymentAccount) {
            $repo = $this->getEntityRepository('RjDataBundle:PaymentAccount');
            $paymentAccount = $repo->find($paymentAccountId);
            $paymentSource = $paymentAccount->getName();
            $this->getEm()->remove($paymentAccount);
            $this->getEm()->flush();
        }

        $orderEncodedId = $this->getIdEncoder()->encode($orderId);
        $response = $this->getRequest($orderEncodedId);
        $this->assertResponse($response);
        $answer = $this->parseContent($response->getContent());

        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        /** @var \CreditJeeves\DataBundle\Entity\Order $order */
        $order = $repo->find($orderId);

        /** Check encoded ID */
        $this->assertEquals($orderEncodedId, $answer['id']);
        $this->assertEquals($orderId, $this->getIdEncoder()->decode($answer['id']));

        /** Check URL */
        $this->assertFullUrl($answer['url']);
        $urlInfo = parse_url($answer['url']);
        $this->assertTrue(strpos($this->prepareUrl($orderEncodedId), $urlInfo['path']) === 0);

        /** Check Contract URL */
        $this->assertFullUrl($answer['contract_url']);
        $response = $this->request($answer['contract_url']);
        $this->assertResponse($response);
        $contract = $this->parseContent($response->getContent());
        $this->assertEquals($order->getContract()->getId(), $this->getIdEncoder()->decode($contract['id']));

        /** Check Payment account URL */
        if ($paymentAccountId && !$deletePaymentAccount) {
            $this->assertFullUrl($answer['payment_account_url']);
            $response = $this->request($answer['payment_account_url']);
            $this->assertResponse($response);
            $paymentAccount = $this->parseContent($response->getContent());
            $this->assertEquals($paymentAccountId, $this->getIdEncoder()->decode($paymentAccount['id']));
            $paymentSource = $paymentAccount['nickname'];
        } else {
            $this->assertEquals('', $answer['payment_account_url']);
        }

        $this->assertEquals($transactionId, $answer['reference_id']);

        $this->assertEquals($paymentSource, $answer['payment_source']);

        $this->assertEquals($answer['type'], $type);

        $this->assertEquals($message, $answer['message']);

        $this->assertEquals($order->getStatus(), $answer['status']);

        $this->assertEquals(number_format($order->getSum(), 2, '.', ''), $answer['total']);

        $paidFor = $paidFor ? (new DateTime($paidFor))->format('Y-m') : '';
        $this->assertEquals($paidFor, $answer['paid_for']);

        $depositedAt = $depositedAt ? (new DateTime($depositedAt))->format('Y-m-d') : '';
        $this->assertEquals($depositedAt, $answer['deposited_at']);
        $this->assertEquals($rent, $answer['rent']);
        $this->assertEquals($other, $answer['other']);
        $this->assertEquals($total, $answer['total']);
    }

    /**
     * @return array
     */
    public static function getWrongOrderDataProvider()
    {
        return [
            [2511139177, 404], // 0
            [656765400, 404], // 1 - Order exists but this user don't have access to it.
        ];
    }

    /**
     * @param int $orderEncodedId
     * @param int $statusCode
     *
     * @test
     * @dataProvider getWrongOrderDataProvider
     */
    public function getWrongOrder($orderEncodedId, $statusCode)
    {
        $response = $this->getRequest($orderEncodedId);

        $this->assertResponse($response, $statusCode);
    }

    /**
     * @test
     * @expectedException \RentJeeves\ApiBundle\Services\Encoders\ValidationEncoderException
     * @expectedExceptionMessage Value "dddde111" isn't correct encrypted Id.
     */
    public function checkInvalidId()
    {
        $this->getWrongOrder('dddde111', 400);
    }
}
