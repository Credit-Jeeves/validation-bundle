<?php

namespace CreditJeeves\DataBundle\Tests\Entity;

use CreditJeeves\DataBundle\Entity\OrderRepository;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManager;
use RentJeeves\TestBundle\BaseTestCase;

class OrderRepositoryCase extends BaseTestCase
{
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
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $tenantRepo = $em->getRepository('RjDataBundle:Tenant');

        $tenant = $tenantRepo->findOneBy(['email' => $userEmail]);
        /** @var OrderRepository $orderRepo */
        $orderRepo = $em->getRepository('DataBundle:Order');

        if ($excludedStatuses !== null) {
            $orders = $orderRepo->getUserOrders($tenant, $excludedStatuses);
        } else {
            $orders = $orderRepo->getUserOrders($tenant);
        }

        $this->assertEquals($count, count($orders));
    }
}
