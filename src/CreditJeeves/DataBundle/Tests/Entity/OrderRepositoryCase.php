<?php

namespace CreditJeeves\DataBundle\Tests\Entity;

use CreditJeeves\DataBundle\Entity\OrderRepository;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManager;
use RentJeeves\LandlordBundle\Accounting\Export\Report\ExportReport;
use RentJeeves\TestBundle\BaseTestCase;
use RentJeeves\DataBundle\Entity\Landlord;
use \DateTime;

class OrderRepositoryCase extends BaseTestCase
{

    protected function setUp()
    {
        /**
         * @var $em EntityManager
         */
        $this->em = $this->getContainer()->get('doctrine')->getManager();
    }

    public static function getUserOrdersDataProvider()
    {
        return [
            [38, 'tenant11@example.com'],
            [38, 'tenant11@example.com', [OrderStatus::NEWONE]],
            [39, 'tenant11@example.com', []],
            [38, 'tenant11@example.com', [OrderStatus::ERROR]],
            [38, 'tenant11@example.com', [OrderStatus::REFUNDED]],
            [37, 'tenant11@example.com', [OrderStatus::RETURNED]],
            [38, 'tenant11@example.com', [OrderStatus::CANCELLED]],
            [6, 'tenant11@example.com', [OrderStatus::COMPLETE]],
            [
                39 - ((39-38) + (39-38) + (39-38) + (39-37) + (39-38)),
                'tenant11@example.com',
                [
                    OrderStatus::NEWONE,
                    OrderStatus::ERROR,
                    OrderStatus::REFUNDED,
                    OrderStatus::RETURNED,
                    OrderStatus::CANCELLED
                ]
            ],
            [0, 'ivan@rentrack.com'],
        ];
    }

    /**
     * @test
     * @dataProvider getUserOrdersDataProvider
     */
    public function getUserOrders($count, $userEmail, $excludedStatuses = null)
    {
        $tenantRepo = $this->em->getRepository('RjDataBundle:Tenant');

        $tenant = $tenantRepo->findOneBy(['email' => $userEmail]);
        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->em->getRepository('DataBundle:Order');

        if ($excludedStatuses !== null) {
            $orders = $orderRepo->getUserOrders($tenant, $excludedStatuses);
        } else {
            $orders = $orderRepo->getUserOrders($tenant);
        }

        $this->assertEquals($count, count($orders));
    }

    public static function getOrdersForReportDataProvider()
    {
        $now = new DateTime();
        $sixMonthsBack = new DateTime();
        $sixMonthsBack->modify("-6 month");

        return [
            [$sixMonthsBack, $now, ExportReport::EXPORT_BY_DEPOSITS],
            [$sixMonthsBack, $now, ExportReport::EXPORT_BY_PAYMENTS],
        ];
    }

    /**
     * @test
     * @dataProvider getOrdersForReportDataProvider
     */
    public function getOrdersForYardiGenesis($startDate, $endDate, $reportType)
    {
        /** @var Landlord $landlord */
        $landlord = $this->em->getRepository('RjDataBundle:Landlord')->findOneBy(['email' => 'landlord1@example.com']);
        $group = $landlord->getCurrentGroup();

        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->em->getRepository('DataBundle:Order');
        $orders = $orderRepo->getOrdersForYardiGenesis(
            $startDate,
            $endDate,
            [$group],
            $reportType
        );

        $this->assertGreaterThan(0, count($orders), 'The report generated no orders and is should have.');

        foreach ($orders as $order) {
            $actualGroupId = $order->getContract()->getGroup()->getId();
            $this->assertEquals($group->getId(), $actualGroupId, 'Detected an Order the is not within Group.');

            $transactionId = $order->getYardiGenesisTransactionId();
            foreach ($order->getTransactions() as $transaction) {
                if ($transaction->getTransactionId() == $transactionId) {
                    $this->assertEquals('complete', $transaction->getStatus(), 'Should not have reversed transactions');
                }
            }
        }
    }

    /**
     * @test
     * @dataProvider getOrdersForReportDataProvider
     */
    public function getOrdersForPromas($startDate, $endDate, $reportType)
    {
        /** @var Landlord $landlord */
        $landlord = $this->em->getRepository('RjDataBundle:Landlord')->findOneBy(['email' => 'landlord1@example.com']);
        $group = $landlord->getCurrentGroup();

        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->em->getRepository('DataBundle:Order');
        $orders = $orderRepo->getOrdersForPromasReport(
            [$group],
            $startDate,
            $endDate,
            $reportType
        );

        $this->assertGreaterThan(0, count($orders), 'The report generated no orders and is should have.');

        foreach ($orders as $order) {
            $actualGroupId = $order->getContract()->getGroup()->getId();
            $this->assertEquals($group->getId(), $actualGroupId, 'Detected an Order the is not within Group.');

            $memo = $order->getPromasMemo();

            foreach ($order->getTransactions() as $transaction) {
                $pattern = sprintf("/.*%s.*/", $transaction->getTransactionId());
                if (preg_match($pattern, $memo) === 1) {
                    $this->assertEquals('complete', $transaction->getStatus(), 'Should not have reversed transactions');
                }
            }
        }
    }

    /**
     * @test
     * @dataProvider getOrdersForReportDataProvider
     */
    public function getOrdersForRealPage($startDate, $endDate, $reportType)
    {
        /** @var Landlord $landlord */
        $landlord = $this->em->getRepository('RjDataBundle:Landlord')->findOneBy(['email' => 'landlord1@example.com']);
        $group = $landlord->getCurrentGroup();

        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->em->getRepository('DataBundle:Order');
        $orders = $orderRepo->getOrdersForRealPageReport(
            [$group],
            $startDate,
            $endDate,
            $reportType
        );

        $this->assertGreaterThan(0, count($orders), 'The report generated no orders and is should have.');

        foreach ($orders as $order) {
            $actualGroupId = $order->getContract()->getGroup()->getId();
            $this->assertEquals($group->getId(), $actualGroupId, 'Detected an Order the is not within Group.');

            $transactionId = $order->getRealPageDocumentNumber();
            foreach ($order->getTransactions() as $transaction) {
                if ($transaction->getTransactionId() == $transactionId) {
                    $this->assertEquals('complete', $transaction->getStatus(), 'Should not have reversed transactions');
                }
            }

        }
    }
}
