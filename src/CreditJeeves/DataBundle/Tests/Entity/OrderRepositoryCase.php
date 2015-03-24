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

    public static function getOrdersForYardiGenesisReportDataProvider()
    {
        $now = new DateTime();
        $sixMonthsBack = new DateTime();
        $sixMonthsBack->modify("-6 month");

        return [
            [$sixMonthsBack, $now],
        ];
    }

    /**
     * @test
     * @dataProvider getOrdersForYardiGenesisReportDataProvider
     */
    public function getOrdersForYardiGenesis($startDate, $endDate)
    {
        /** @var Landlord $landlord */
        $landlord = $this->em->getRepository('RjDataBundle:Landlord')->findOneBy(['email' => 'landlord1@example.com']);
        $groupId = $landlord->getCurrentGroup()->getId();

        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->em->getRepository('DataBundle:Order');
        $orders = $orderRepo->getOrdersForYardiGenesis(
            $startDate,
            $endDate,
            $groupId,
            ExportReport::EXPORT_BY_DEPOSITS
        );

        $this->assertGreaterThan(0, count($orders), "The report generated no orders and is should have.");

        foreach ($orders as $order) {
            $actualGroupId = $order->getContract()->getGroup()->getId();
            $this->assertEquals($groupId, $actualGroupId, "Detected an Order the is not within Group.");
        }
    }
}
