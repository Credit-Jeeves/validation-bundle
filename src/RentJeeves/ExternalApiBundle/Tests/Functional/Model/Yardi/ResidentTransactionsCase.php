<?php

namespace RentJeeves\ExternalApiBundle\Tests\Functional\Model\Yardi;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use JMS\Serializer\SerializationContext;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\DataBundle\Enum\PaymentTypeScannedCheck;
use RentJeeves\DataBundle\Enum\SynchronizationStrategy;
use RentJeeves\DataBundle\Enum\YardiPostMonthOption;
use RentJeeves\ExternalApiBundle\Model\Yardi\ResidentTransactions;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class ResidentTransactionsCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldNotSetPostMonthNodeOfPostMonthOptionIsNone()
    {
        $em = $this->getEntityManager();
        /** @var YardiSettings $yardiSettings */
        $yardiSettings = $em->find('RjDataBundle:YardiSettings', 1);
        $this->assertNotNull($yardiSettings, 'YardiSettings not found');
        $yardiSettings->setPostMonthNode(YardiPostMonthOption::NONE);
        $em->flush($yardiSettings);
        /** @var Order $order */
        $order = $em->find('DataBundle:Order', 2);
        $this->assertNotNull($order, 'Order with ID#2 not found');

        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('baseRequest');

        $residentTransactions = new ResidentTransactions(
            $yardiSettings,
            [$order]
        );
        $serializer = $this->getContainer()->get('jms_serializer');
        $xml = $serializer->serialize(
            $residentTransactions,
            'xml',
            $context
        );

        $this->assertNotContains('<PostMonth>', $xml);
    }

    /**
     * @test
     * @dataProvider provideData
     */
    public function shouldSetCorrectPostMonthForGivenParameters(
        $postMonthNode,
        $syncStrategy,
        $paymentProcessor,
        $paymentType,
        \DateTime $createdAt,
        \DateTime $depositedAt,
        $expectedResult
    ) {
        $em = $this->getEntityManager();
        /** @var YardiSettings $yardiSettings */
        $yardiSettings = $em->find('RjDataBundle:YardiSettings', 1);
        $this->assertNotNull($yardiSettings, 'YardiSettings not found');
        $yardiSettings->setPostMonthNode($postMonthNode);
        $yardiSettings->setSynchronizationStrategy($syncStrategy);

        /** @var Order $order */
        $order = $em->find('DataBundle:Order', 2);
        $this->assertNotNull($order, 'Order with ID#2 not found');

        $order->setCreatedAt($createdAt);
        $order->setPaymentProcessor($paymentProcessor);
        $order->getCompleteTransaction()->setDepositDate($depositedAt);
        $order->setPaymentType($paymentType);

        $em->flush();

        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('withPostMonth');

        $residentTransactions = new ResidentTransactions(
            $yardiSettings,
            [$order]
        );
        $serializer = $this->getContainer()->get('jms_serializer');
        $xml = $serializer->serialize(
            $residentTransactions,
            'xml',
            $context
        );

        $this->assertContains($expectedResult, $xml);
    }

    /**
     * @return array
     */
    public function provideData()
    {
        return [
            [
                YardiPostMonthOption::TRANSACTION_DATE,
                SynchronizationStrategy::REAL_TIME,
                PaymentProcessor::HEARTLAND,
                OrderPaymentType::BANK,
                new \DateTime('2015-12-01'),
                new \DateTime('2016-01-01'),
                '<PostMonth>2015-12-01</PostMonth>' // PostMonthNode = TRANSACTION_DATE, use created_at
            ],
            [
                YardiPostMonthOption::TRANSACTION_DATE,
                SynchronizationStrategy::DEPOSITED,
                PaymentProcessor::HEARTLAND,
                OrderPaymentType::BANK,
                new \DateTime('2015-12-01'),
                new \DateTime('2016-01-01'),
                '<PostMonth>2015-12-01</PostMonth>' // PostMonthNode = TRANSACTION_DATE, use created_at
            ],
            [
                YardiPostMonthOption::DEPOSIT_DATE,
                SynchronizationStrategy::REAL_TIME,
                PaymentProcessor::HEARTLAND,
                OrderPaymentType::BANK,
                new \DateTime('2015-12-01'),
                new \DateTime('2016-01-01'),
                '<PostMonth>2015-12-04</PostMonth>' // PostMonthNode = DEPOSIT_DATE, calc deposit date
            ],
            [
                YardiPostMonthOption::DEPOSIT_DATE,
                SynchronizationStrategy::DEPOSITED,
                PaymentProcessor::HEARTLAND,
                OrderPaymentType::BANK,
                new \DateTime('2015-12-01'),
                new \DateTime('2016-01-01'),
                '<PostMonth>2016-01-01</PostMonth>' // PostMonthNode = DEPOSIT_DATE + sync = DEPOSITED, use deposit date
            ],
            [
                YardiPostMonthOption::DEPOSIT_DATE,
                SynchronizationStrategy::DEPOSITED,
                PaymentProcessor::HEARTLAND,
                OrderPaymentType::CARD,
                new \DateTime('2015-12-05'),
                new \DateTime('2016-01-01'),
                '<PostMonth>2015-12-08</PostMonth>' // +2 business days for HPS card
            ],
            [
                YardiPostMonthOption::TRANSACTION_DATE,
                SynchronizationStrategy::REAL_TIME,
                PaymentProcessor::ACI,
                OrderPaymentType::BANK,
                new \DateTime('2015-12-09'),
                new \DateTime('2016-01-01'),
                '<PostMonth>2015-12-09</PostMonth>' // PostMonthNode = TRANSACTION_DATE + REAL_TIME, use created_at
            ],
            [
                YardiPostMonthOption::DEPOSIT_DATE,
                SynchronizationStrategy::DEPOSITED,
                PaymentProcessor::ACI,
                OrderPaymentType::CARD,
                new \DateTime('2015-12-01'),
                new \DateTime('2016-01-01'),
                '<PostMonth>2015-12-02</PostMonth>' // next business day for ACI card
            ],
        ];
    }

    /**
     * @test
     */
    public function shouldSetCorrectPaymentTypeForScannedCheck()
    {
        $this->load(true);
        $em = $this->getEntityManager();
        /** @var YardiSettings $yardiSettings */
        $yardiSettings = $em->find('RjDataBundle:YardiSettings', 1);
        $this->assertNotNull($yardiSettings, 'YardiSettings not found');
        $yardiSettings->setPaymentTypeScannedCheck(PaymentTypeScannedCheck::CASH);

        /** @var Order $order */
        $order = $em->find('DataBundle:Order', 2);
        $this->assertNotNull($order, 'Order with ID#2 not found');

        $order->setPaymentProcessor(PaymentProcessor::PROFIT_STARS);
        $order->setPaymentType(OrderPaymentType::SCANNED_CHECK);

        $em->flush();

        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('withPostMonth');

        $residentTransactions = new ResidentTransactions(
            $yardiSettings,
            [$order]
        );
        $serializer = $this->getContainer()->get('jms_serializer');
        $xml = $serializer->serialize(
            $residentTransactions,
            'xml',
            $context
        );

        $this->assertContains('<Payment Type="Cash">', $xml);
    }
}
